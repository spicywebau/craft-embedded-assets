import '../styles/main.scss'
import EmbeddedAssets from './EmbeddedAssets'
import Button from './Button'
import { monkeypatch } from './utilities'

declare global {
  interface Window {
    EmbeddedAssets: EmbeddedAssets
    EmbeddedAssetsPreviewMap?: Map<string, FrameRequestCallback>
  }
}

interface EmbeddableAssetIndex {
  $uploadButton?: JQuery | null
  settings: { criteria?: { kind?: string | string[] } }
  sourceKey: string
  sourcePath?: Array<{ folderId?: number }>
  view?: {
    selectElementById: (id: string) => void
    elementSelect?: { $selectedItems: JQuery }
  }
  on: (events: string, handler: Function) => void
  updateElements: () => void
}

interface IndexButtonState {
  button: Button
  replaceButton: Button
  idsToSelect: string[]
}

const embeddedAssets = new EmbeddedAssets()
const indexStates = new WeakMap<EmbeddableAssetIndex, IndexButtonState>()
let activeIndex: EmbeddableAssetIndex | null = null

const placeButtons = (assetIndex: EmbeddableAssetIndex, state: IndexButtonState): void => {
  const $uploadButton = assetIndex.$uploadButton
  const $embedElement = state.button.$element
  const $replaceElement = state.replaceButton.$element

  if ($uploadButton == null || $embedElement === null || $replaceElement === null) {
    return
  }

  if ($uploadButton.closest('#header').length > 0) {
    $uploadButton.before($embedElement)
    $uploadButton.before($replaceElement)
  } else if ($uploadButton.closest('.modal').length > 0) {
    $uploadButton.after($embedElement)
    $uploadButton.after($replaceElement)
  }
}

const resetButtons = (assetIndex: EmbeddableAssetIndex, state: IndexButtonState): void => {
  // Empty array just means no file type restrictions
  let allowedKinds = assetIndex.settings.criteria?.kind ?? []

  if (typeof allowedKinds === 'string') {
    allowedKinds = [allowedKinds]
  }

  // We still need to check the array length, because `allowedKinds` will be an empty array if the
  // asset field had no restriction on allowed file types
  if (allowedKinds.length > 0 && !allowedKinds.includes('json')) {
    state.button.hide()
  } else {
    state.button.show()
  }

  state.replaceButton.hide()
}

const setUpIndex = (assetIndex: EmbeddableAssetIndex, $uploadButton: JQuery): void => {
  const state: IndexButtonState = {
    button: new Button('Embed'),
    replaceButton: new Button('Replace'),
    idsToSelect: []
  }
  let replaceAssetId = ''

  indexStates.set(assetIndex, state)
  placeButtons(assetIndex, state)
  resetButtons(assetIndex, state)

  let modalOrientations

  if ($uploadButton.closest('#header').length > 0) {
    modalOrientations = ['bottom', 'left', 'right', 'top']
  } else if ($uploadButton.closest('.modal').length > 0) {
    modalOrientations = ['top', 'right', 'bottom', 'left']
  }

  const getActionTarget: () => Object = () => {
    const sourcePath = assetIndex.sourcePath ?? []

    if (sourcePath.length > 0) {
      // Craft 4.4 subfolder compatibility
      const currentFolder = sourcePath[sourcePath.length - 1]

      if (typeof currentFolder.folderId !== 'undefined') {
        return {
          targetType: 'folder',
          targetId: currentFolder.folderId
        }
      }
    }

    const split = assetIndex.sourceKey.split(':')

    if (typeof split[split.length - 2] !== 'undefined') {
      return {
        targetType: split[split.length - 2],
        targetUid: split[split.length - 1]
      }
    }

    return {}
  }

  // These listeners must be bound before `addButton()` binds its own, so the active index and
  // replacement target are already set when the modal opens
  state.button.$element?.on('click', () => {
    activeIndex = assetIndex
  })

  state.replaceButton.$element?.on('click', () => {
    activeIndex = assetIndex
    embeddedAssets.setReplaceAssetId(replaceAssetId)
  })

  embeddedAssets.addButton(state.button, modalOrientations, getActionTarget)
  embeddedAssets.addButton(state.replaceButton, modalOrientations, getActionTarget, true)

  assetIndex.on('updateElements', () => {
    state.idsToSelect.forEach((id) => assetIndex.view?.selectElementById(id))
    state.idsToSelect = []

    resetButtons(assetIndex, state)
  })

  assetIndex.on('selectionChange', () => {
    const $selectedItems = assetIndex.view?.elementSelect?.$selectedItems
    const $selectedItem = $selectedItems != null && $selectedItems.length === 1 ? $selectedItems.eq(0) : null

    if ($selectedItem !== null && $selectedItem.find('[data-embedded-asset]').length > 0) {
      state.button.hide()
      state.replaceButton.show()

      replaceAssetId = $selectedItem.attr('data-id') ?? ''
    } else {
      resetButtons(assetIndex, state)
    }
  })
}

embeddedAssets.on('save', (e: { assetId: string }) => {
  const state = activeIndex !== null ? indexStates.get(activeIndex) : undefined

  if (activeIndex === null || typeof state === 'undefined') {
    return
  }

  state.idsToSelect.push(e.assetId)
  activeIndex.updateElements()
})

monkeypatch(Craft.AssetIndex, 'createUploadInputs', function (this: EmbeddableAssetIndex) {
  const $uploadButton = this.$uploadButton

  // If there's no upload button, there should be no embed button
  if (typeof $uploadButton === 'undefined' || $uploadButton === null) {
    return
  }

  const state = indexStates.get(this)

  if (typeof state !== 'undefined') {
    // Craft recreates its upload button whenever a source is selected, so put this index's
    // buttons back beside the new one and recheck whether embedding is allowed here
    placeButtons(this, state)
    resetButtons(this, state)
  } else {
    setUpIndex(this, $uploadButton)
  }
})

monkeypatch(Craft.AssetIndex, 'destroy', function (this: EmbeddableAssetIndex) {
  const state = indexStates.get(this)

  if (typeof state === 'undefined') {
    return
  }

  embeddedAssets.removeButton(state.button)
  embeddedAssets.removeButton(state.replaceButton)
  state.button.destroy()
  state.replaceButton.destroy()

  if (activeIndex === this) {
    activeIndex = null
  }

  indexStates.delete(this)
})

window.EmbeddedAssets = embeddedAssets
