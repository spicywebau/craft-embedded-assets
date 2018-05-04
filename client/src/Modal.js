import $ from 'jquery'
import Garnish from 'garnish'
import Form from './Form'

export default class Modal
{
	constructor(settings = {})
	{
		this.settings = settings
		this.form = null
		this.hud = null
	}

	create($target, settings = {})
	{
		settings = Object.assign({
			mainClass: 'embedded-assets_hud',
			minBodyWidth: 400,
		}, settings)

		this.form = new Form()
		this.hud = new Garnish.HUD($target, this.form.$element, settings)

		this._callEvent('onCreate')

		this.hud.on('show', () => this._callEvent('onShow'))
		this.hud.on('hide', () => this._callEvent('onHide'))
	}

	destroy()
	{
		this.form.destroy()
		this.form = null

		this.hud.hide()
		this.hud.$hud.remove()
		this.hud.$shade.remove()
		this.hud = null

		this._callEvent('onDestroy')
	}

	show($target, settings = {})
	{
		if (this.hud)
		{
			this.hud.setSettings(settings)
			this.hud.$trigger = $($target)

			if (this.hud.showing)
			{
				this.hud.queueUpdateSizeAndPosition()
			}
			else
			{
				this.hud.show()
			}
		}
		else
		{
			this.create($target, settings)
		}

		this._callEvent('onShow')
	}

	hide()
	{
		if (this.hud)
		{
			this.hud.hide()
		}

		this._callEvent('onHide')
	}

	isShowing()
	{
		return this.hud.showing
	}

	_callEvent(event)
	{
		if (typeof this.settings[event] === 'function')
		{
			this.settings[event].call(this)
		}
	}
}
