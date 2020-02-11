import $ from 'jquery'
import Craft from 'craft'

export default class Button
{
	constructor(name)
	{
		this.$element = $('<div class="embedded-assets_button btn" data-icon="globe">Embed</div>')
	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
	}

	setLabel(label)
	{
		this.$element.text(label)
	}

	show(){
		this.$element.show()
	}

	hide(){
		this.$element.hide()
	}

	setActive(flag = true)
	{
		this.$element.toggleClass('active', flag)
	}
}
