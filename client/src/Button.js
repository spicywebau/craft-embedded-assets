import $ from 'jquery'
import Garnish from 'garnish'
import Craft from 'craft'

export default class Button
{
	constructor()
	{
		this._active = false

		this.$element = null
	}

	create()
	{
		const label = Craft.t('embeddedassets', "Embed")

		this.$element = $(`
			<div class="embedded-assets_button btn" data-icon="globe">${label}</div>
		`)

		this._update()
	}

	destroy()
	{
		this.$element.remove()

		$this.$element = null
	}

	setActive(flag = true)
	{
		this._active = flag
		this._update()
	}

	_update()
	{
		if (this.$element)
		{
			this.$element.toggleClass('active', this._active)
		}
	}
}
