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
	const replaceButton = new Button('replace')

	const $uploadButton = this.$uploadButton
	const inHeader = $uploadButton.closest('#header').length > 0
	const inModal = $uploadButton.closest('.modal').length > 0

	let modalOrientations

	if (inHeader)
	{
		replaceButton.$element.css('display', 'none');
		this.$uploadButton.before(replaceButton.$element)
		this.$uploadButton.before(button.$element)
		modalOrientations = ['bottom', 'left', 'right', 'top']
	}
	else if (inModal)
	{
		this.$uploadButton.after(button.$element)
		modalOrientations = ['top', 'right', 'bottom', 'left']
	}

	const getFolderId = () => {
		const split = this.getDefaultSourceKey().split(':');

        if (split[split.length - 1])
        {
            return split[split.length - 1];
        }

        return 0;
	};

	embeddedAssets.addButton(button, modalOrientations, getFolderId)

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

		let kinds = this.settings.criteria.kind;

		if (kinds && Array.isArray(kinds) && kinds.length > 0) {
			if (kinds.indexOf('json') === -1) {
				button.$element.css('display', 'none');
			} else {
				button.$element.css('display', '');
			}
		}
	})

	this.on('selectionChange', (e) => {
		let selectedItems = e.target.view.elementSelect.$selectedItems

		if (selectedItems.length && selectedItems.length === 1) {
			let findAssetEl = $(selectedItems[0]).find('[data-embedded-asset]')

			if (findAssetEl.length) {
				// show replace button
				replaceButton.$element.css('display', '');
			} else {
				//	hide replace button
				replaceButton.$element.css('display', 'none');
			}
		} else {
			//	hide replace button
			replaceButton.$element.css('display', 'none');
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
		this.$fieldsContainer.find('.image-preview-container').remove()

		const preview = new Preview()
		const paddingTop = Math.min(embedRatio ? embedRatio : 100, 75) + '%'

		preview.$element.css({ paddingTop })

		this.$fieldsContainer.find('.field.first').before(preview.$element)

		requestAnimationFrame(() => preview.request({ assetId, showContent: false }))
	}
})

window.EmbeddedAssets = embeddedAssets
