import './main.scss'
import Craft from 'craft'
import EmbeddedAssets from './classes/EmbeddedAssets'
import Button from './classes/Button'
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

	const getFolderId = () => this.getDefaultSourceKey().split(':')[1]|0

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

})

window.EmbeddedAssets = embeddedAssets
