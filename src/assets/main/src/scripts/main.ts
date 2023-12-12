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

monkeypatch(Craft.AssetIndex, 'init', function () {
  const button = new Button('Embed')
  const replaceButton = new Button('Replace')

  const $uploadButton = this.$uploadButton

  // If there's no upload button, there should be no embed button
  if (typeof $uploadButton === 'undefined' || $uploadButton === null) {
    return
  }

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
    if (this.sourcePath?.length > 0 ?? false) {
      // Craft 4.4 subfolder compatibility
      const currentFolder = this.sourcePath[this.sourcePath.length - 1]

      if (typeof currentFolder.folderId !== 'undefined') {
        return {
          targetType: 'folder',
          targetId: currentFolder.folderId
        }
      }
    }

    const split = this.sourceKey.split(':')

    if (typeof split[split.length - 2] !== 'undefined') {
      return {
        targetType: split[split.length - 2],
        targetUid: split[split.length - 1]
      }
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
