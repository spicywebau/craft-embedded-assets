import './main.scss'
import $ from 'jquery'
import Craft from 'craft'
import EmbeddedAssets from './classes/EmbeddedAssets'
import Button from './classes/Button'
import { monkeypatch } from './utilities'

const embeddedAssets = new EmbeddedAssets()

monkeypatch(Craft.AssetIndex, 'init', function () {
  const button = new Button('Embed')
  const replaceButton = new Button('Replace')

  const $uploadButton = this.$uploadButton
  const inHeader = $uploadButton.closest('#header').length > 0
  const inModal = $uploadButton.closest('.modal').length > 0

  // Empty array just means no file type restrictions
  const allowedAssetKinds = this.settings.criteria ? this.settings.criteria.kind : []

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

  const showButtonIfJsonAllowed = (button, allowedKinds) => {
    if (typeof allowedKinds === 'string') {
      allowedKinds = [allowedKinds]
    }

    // We still need to check the array length, because `allowedKinds` will be an empty array if the
    // asset field had no restriction on allowed file types
    if (allowedKinds && allowedKinds.length > 0 && allowedKinds.indexOf('json') === -1) {
      button.hide()
    } else {
      button.show()
    }
  }

  showButtonIfJsonAllowed(button, allowedAssetKinds)
  replaceButton.hide()

  const getActionTarget = () => {
    const split = this.sourceKey.split(':')

    if (split[split.length - 2]) {
      return {
        targetType: split[split.length - 2],
        targetUid: split[split.length - 1]
      }
    }

    return {}
  }

  embeddedAssets.addButton(button, modalOrientations, getActionTarget)
  embeddedAssets.addButton(replaceButton, modalOrientations, getActionTarget, true)

  let idsToSelect = []

  embeddedAssets.on('save', e => {
    idsToSelect.push(e.assetId)
    this.updateElements()
  })

  this.on('updateElements', () => {
    idsToSelect.forEach((id) => this.view.selectElementById(id))
    idsToSelect = []

    showButtonIfJsonAllowed(button, allowedAssetKinds)
    replaceButton.hide()
  })

  this.on('selectionChange', (e) => {
    const selectedItems = e.target.view.elementSelect.$selectedItems

    if (selectedItems.length && selectedItems.length === 1) {
      const findAssetEl = $(selectedItems[0]).find('[data-embedded-asset]')

      if (findAssetEl.length) {
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
