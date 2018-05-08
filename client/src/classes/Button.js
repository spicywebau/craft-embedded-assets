import $ from 'jquery'
import Craft from 'craft'

export default class Button
{
	constructor()
	{
		const label = Craft.t('embeddedassets', `Embed`)
		this.$element = $(`<div class="embedded-assets_button btn" data-icon="globe">${label}</div>`)
	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
	}

	setActive(flag = true)
	{
		this.$element.toggleClass('active', flag)
	}
}
