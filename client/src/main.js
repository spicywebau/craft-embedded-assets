import Craft from 'craft'
import './main.scss'
import EmbeddedAssets from './classes/EmbeddedAssets'
import Button from './classes/Button'
import { monkeypatch } from './utilities'

const embeddedAssets = new EmbeddedAssets()
embeddedAssets.create()

monkeypatch(Craft.AssetIndex, 'init', function(assetIndex)
{
	const button = new Button()
	button.create()

	const $uploadButton = assetIndex.$uploadButton
	const inHeader = $uploadButton.closest('#header').length > 0
	const inModal = $uploadButton.closest('.modal').length > 0

	let modalOrientations

	if (inHeader)
	{
		assetIndex.$uploadButton.before(button.$element)
		modalOrientations = ['bottom', 'left', 'right', 'top']
	}
	else if (inModal)
	{
		assetIndex.$uploadButton.after(button.$element)
		modalOrientations = ['top', 'right', 'bottom', 'left']
	}

	embeddedAssets.addButton(button, modalOrientations)
})

window.EmbeddedAssets = embeddedAssets
