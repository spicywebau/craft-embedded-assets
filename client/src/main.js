import './main.scss'
import Craft from 'craft'
import EmbeddedAssets from './classes/EmbeddedAssets'
import Button from './classes/Button'
import Preview from './classes/Preview'
import { monkeypatch } from './utilities'

const embeddedAssets = new EmbeddedAssets()

monkeypatch(Craft.AssetIndex, 'init', function()
{
	const button = new Button()
	const replaceButton = new Button()

	replaceButton.setLabel('Replace')

	const $uploadButton = this.$uploadButton
	const inHeader = $uploadButton.closest('#header').length > 0
	const inModal = $uploadButton.closest('.modal').length > 0

	let modalOrientations

	if (inHeader)
	{
		this.$uploadButton.before(button.$element)
		this.$uploadButton.before(replaceButton.$element)
		modalOrientations = ['bottom', 'left', 'right', 'top']
	}
	else if (inModal)
	{
		this.$uploadButton.after(button.$element)
		this.$uploadButton.after(replaceButton.$element)
		modalOrientations = ['top', 'right', 'bottom', 'left']
	}

	const showButtonIfJsonAllowed = (button, allowedKinds) => {
		if (typeof allowedKinds === 'string') {
			allowedKinds = [allowedKinds];
		}

		if (allowedKinds && allowedKinds.indexOf('json') === -1) {
			button.hide()
		} else {
			button.show()
		}
	}

	showButtonIfJsonAllowed(button, this.settings.criteria.kind)
	replaceButton.hide()

	const getFolderId = () => {
		const split = this.sourceKey.split(':');

		if (split[split.length - 1])
		{
			return split[split.length - 1];
		}

		return 0;
	};

	embeddedAssets.addButton(button, modalOrientations, getFolderId)
	embeddedAssets.addButton(replaceButton, modalOrientations, getFolderId, true)

	let idsToSelect = []

	embeddedAssets.on('save', e =>
	{
		idsToSelect.push(e.assetId)
		this.updateElements()
	})

	this.on('updateElements', () =>
	{
		idsToSelect.forEach((id) => this.view.selectElementById(id))
		idsToSelect = []

		showButtonIfJsonAllowed(button, this.settings.criteria.kind)
		replaceButton.hide()
	})

	this.on('selectionChange', (e) => {
		let selectedItems = e.target.view.elementSelect.$selectedItems

		if (selectedItems.length && selectedItems.length === 1) {
			let findAssetEl = $(selectedItems[0]).find('[data-embedded-asset]')

			if (findAssetEl.length) {
				button.hide()
				replaceButton.show()

				embeddedAssets.setReplaceAssetId(selectedItems[0].attributes['data-id'].value)
			} else {
				button.show()
				replaceButton.hide()
			}
		} else {
			button.show()
			replaceButton.hide()
		}
	})
})

monkeypatch(Craft.AssetEditor, 'updateForm', function()
{
	const assetId = this.$element.attr('data-id')
	const dataEmbedRatio = this.$element.attr('data-embedded-asset')
	let embedRatio = dataEmbedRatio

	if (assetId && typeof embedRatio !== 'undefined')
	{
		if (typeof embedRatio == 'string') {
			embedRatio = '56.25'
		}

		// Won't be needing this anymore
		this.$fieldsContainer.find('.preview-thumb-container').remove()

		const preview = new Preview()
		const paddingTop = Math.min(embedRatio ? embedRatio : 100, 75) + '%'

		preview.$element.css({ paddingTop })

		this.$fieldsContainer.find('.field.first').before(preview.$element)

		requestAnimationFrame(() => preview.request({ assetId, showContent: false }))
	}
})

window.EmbeddedAssets = embeddedAssets
