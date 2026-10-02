import '../styles/main.scss'
import * as $ from 'jquery'
import EmbeddedAssets from './EmbeddedAssets'
import Button from './Button'
import { monkeypatch } from './utilities'

declare global {
  interface Window {
    EmbeddedAssets: EmbeddedAssets
    EmbeddedAssetsPreviewMap?: Map<string, FrameRequestCallback>
  }
}

const embeddedAssets = new EmbeddedAssets()
const button = new Button('Embed')
const replaceButton = new Button('Replace')
let buttonInit = false

monkeypatch(Craft.AssetIndex, 'createUploadInputs', function () {
  if (buttonInit) {
    // At least make sure it's in the right place
    const inHeader = this.$uploadButton.closest('#header').length > 0
    const inModal = this.$uploadButton.closest('.modal').length > 0

    if (inHeader) {
      this.$uploadButton.before(button.$element)
      this.$uploadButton.before(replaceButton.$element)
    } else if (inModal) {
      this.$uploadButton.after(button.$element)
      this.$uploadButton.after(replaceButton.$element)
    }

    return
  }

  const $uploadButton = this.$uploadButton

  // If there's no upload button, there should be no embed button
  if (typeof $uploadButton === 'undefined' || $uploadButton === null) {
    return
  }

  buttonInit = true
  const inHeader = $uploadButton.closest('#header').length > 0
  const inModal = $uploadButton.closest('.modal').length > 0

  // Empty array just means no file type restrictions
  const allowedAssetKinds = this.settings.criteria?.kind ?? []

  let modalOrientations

  if (inHeader) {
    this.$uploadButton.before(button.$element)
    this.$uploadButton.before(replaceButton.$element)
    modalOrientations = ['bottom', 'left', 'right', 'top']
  } else if (inModal) {
    this.$uploadButton.after(button.$element)
    this.$uploadButton.after(replaceButton.$element)
    modalOrientations = ['top', 'right', 'bottom', 'left']
  }

  const showButtonIfJsonAllowed: (button: Button, allowedKinds: string | string[]) => void = (button, allowedKinds = []) => {
    if (typeof allowedKinds === 'string') {
      allowedKinds = [allowedKinds]
    }

    // We still need to check the array length, because `allowedKinds` will be an empty array if the
    // asset field had no restriction on allowed file types
    if (allowedKinds.length > 0 && !allowedKinds.includes('json')) {
      button.hide()
    } else {
      button.show()
    }
  }

  showButtonIfJsonAllowed(button, allowedAssetKinds)
  replaceButton.hide()

  // Set whenever getActionTarget() has to fall back to fieldId/elementId
  // because no real folder/source could be resolved from the modal's current
  // sourcePath/sourceKey (see below). In that case the folder the asset was
  // just saved into is a brand new dynamic subfolder that was never rendered
  // as a source in this modal, so there's nothing for updateElements() to
  // refresh into -- the asset has to be injected straight into the field instead.
  let usedFieldFallback = false

  const getActionTarget: () => Object = () => {
    usedFieldFallback = false

    const sourcePathLength = this.sourcePath?.length ?? 0

    if (sourcePathLength > 0) {
      // Craft 4.4 subfolder compatibility
      const currentFolder = this.sourcePath[sourcePathLength - 1]

      if (typeof currentFolder.folderId !== 'undefined') {
        return {
          targetType: 'folder',
          targetId: currentFolder.folderId
        }
      }
    }

    const split = (this.sourceKey ?? '').split(':')

    if (typeof split[split.length - 2] !== 'undefined') {
      return {
        targetType: split[split.length - 2],
        targetUid: split[split.length - 1]
      }
    }

    // No real folder/source could be resolved
    // fall back to temp pseudo-source with empty sourcePath
    // let the server resolve (and create) the folder the same way Craft's own asset upload does.
    const fieldIdMatch = /\.field\.(\d+)/.exec(this.settings.storageKey ?? '')

    if (fieldIdMatch !== null) {
      const target: { fieldId: number, elementId?: number } = { fieldId: Number(fieldIdMatch[1]) }
      const elementId = this.settings.referenceElementId ?? this.settings.referenceElementOwnerId

      if (elementId != null) {
        target.elementId = elementId
      }

      usedFieldFallback = true

      return target
    }

    return {}
  }

  embeddedAssets.addButton(button, modalOrientations, getActionTarget)
  embeddedAssets.addButton(replaceButton, modalOrientations, getActionTarget, true)

  let idsToSelect: string[] = []

  // Adds the newly-created asset to the field directly, bypassing the
  // modal's element listing entirely. Used when the asset was saved into a
  // dynamic subfolder that didn't exist yet when the modal was opened (see
  // usedFieldFallback above) -- there's no source in the modal's sidebar for
  // that folder, so updateElements() has nothing to refresh into.
  //
  // modal.onSelect() is exactly what Craft calls when you click the modal's
  // own "Select" button (see Craft.BaseElementSelectorModal::selectElements()
  // and Craft.BaseElementSelectInput::onModalSelect()) -- that handler does
  // its own app/render-elements call and hides the modal itself, so all it
  // needs from us is the id/siteId, not a fully-rendered element.
  const selectEmbeddedAssetInField = (assetId: string): void => {
    const modal = this.settings.modal

    if (typeof modal?.onSelect !== 'function') {
      idsToSelect.push(assetId)
      this.updateElements()
      return
    }

    try {
      modal.onSelect([{
        id: assetId,
        // this.settings.criteria?.siteId is frequently undefined (and gets
        // dropped entirely by JSON.stringify, tripping an "undefined array
        // key" error server-side in app/render-elements) -- this.siteId is
        // the element index's own always-populated current site, used the
        // same way internally for its own element queries.
        siteId: this.siteId ?? this.settings.criteria?.siteId ?? null
      }])
    } catch (e) {
      console.error('Embedded Assets: failed to add the new asset to the field', e)

      // At least the upload itself succeeded -- fall back to trying to
      // refresh the current listing rather than losing the asset silently.
      idsToSelect.push(assetId)
      this.updateElements()
    }
  }

  embeddedAssets.on('save', (e: any) => {
    if (usedFieldFallback) {
      selectEmbeddedAssetInField(e.assetId)
    } else {
      idsToSelect.push(e.assetId)
      this.updateElements()
    }
  })

  this.on('updateElements', () => {
    idsToSelect.forEach((id) => this.view.selectElementById(id))
    idsToSelect = []

    showButtonIfJsonAllowed(button, allowedAssetKinds)
    replaceButton.hide()
  })

  this.on('selectionChange', (e: any) => {
    const selectedItems = e.target.view?.elementSelect.$selectedItems ?? []

    if (selectedItems.length === 1) {
      const findAssetEl = $(selectedItems[0]).find('[data-embedded-asset]')

      if (findAssetEl.length > 0) {
        button.hide()
        replaceButton.show()

        embeddedAssets.setReplaceAssetId(selectedItems[0].attributes['data-id'].value)
      } else {
        showButtonIfJsonAllowed(button, allowedAssetKinds)
        replaceButton.hide()
      }
    } else {
      showButtonIfJsonAllowed(button, allowedAssetKinds)
      replaceButton.hide()
    }
  })
})

window.EmbeddedAssets = embeddedAssets
