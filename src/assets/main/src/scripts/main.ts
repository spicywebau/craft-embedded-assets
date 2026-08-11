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

  const getActionTarget: () => Object = () => {
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

    // No real folder/source could be resolved -- this happens when an Assets
    // field is restricted to a single folder with a dynamic (Twig) subpath
    // that hasn't been created yet, so Craft's asset index falls back to the
    // "temp" pseudo-source with an empty sourcePath. Fall back to the same
    // fieldId/elementId params Craft's own AssetSelectInput uploader sends in
    // that situation, and let the server resolve (and create) the folder.
    if (this.settings.fieldId != null) {
      const target: { fieldId: number, elementId?: number } = { fieldId: this.settings.fieldId }

      if (this.settings.sourceElementId != null) {
        target.elementId = this.settings.sourceElementId
      }

      return target
    }

    return {}
  }

  embeddedAssets.addButton(button, modalOrientations, getActionTarget)
  embeddedAssets.addButton(replaceButton, modalOrientations, getActionTarget, true)

  let idsToSelect: string[] = []

  embeddedAssets.on('save', (e: any) => {
    idsToSelect.push(e.assetId)
    this.updateElements()
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
