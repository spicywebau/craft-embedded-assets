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

	const $uploadButton = this.$uploadButton
	const inHeader = $uploadButton.closest('#header').length > 0
	const inModal = $uploadButton.closest('.modal').length > 0

	let modalOrientations

	if (inHeader)
	{
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
	})
})

monkeypatch(Craft.AssetEditor, 'updateForm', function()
{
	const assetId = this.$element.attr('data-id')
	const embedRatio = this.$element.attr('data-embedded-asset')

	if (assetId && typeof embedRatio !== 'undefined')
	{
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
