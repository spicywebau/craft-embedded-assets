import $ from 'jquery'
import Garnish from 'garnish'
import Emitter from './Emitter'
import Form from './Form'
import { uniqueId } from '../utilities'

export default class Modal extends Emitter
{
	constructor(getFolderId = ()=>-1)
	{
		super()

		this._getFolderId = getFolderId

		this.form = null
		this.hud = null
	}

	create($target, settings = {})
	{
		settings = Object.assign({
			hudClass: 'hud embedded-assets_hud',
			mainClass: 'embedded-assets_hud_main',
			minBodyWidth: 400,
		}, settings)

		const cancelId = uniqueId()
		const saveId = uniqueId()
		const spinnerId = uniqueId()
		const cancelLabel = Craft.t('app', `Cancel`)
		const saveLabel = Craft.t('app', `Save`)

		this.$footer = $(`
			<div class="hud-footer embedded-assets_hud_footer">
				<div class="buttons right">
					<div id="${cancelId}" class="btn">${cancelLabel}</div>
					<div id="${saveId}" class="btn submit">${saveLabel}</div>
					<div id="${spinnerId}" class="spinner hidden"></div>
				</div>
			</div>
		`)

		this.$cancel = this.$footer.find(`#${cancelId}`)
		this.$save = this.$footer.find(`#${saveId}`)
		this.$spinner = this.$footer.find(`#${spinnerId}`)

		this.form = new Form(this._getFolderId)
		this.hud = new Garnish.HUD($target, this.form.$element.add(this.$footer), settings)

		this.trigger('create')

		this.$save.on('click', e => this.$save.hasClass('disabled') && e.stopImmediatePropagation())

		this.$cancel.on('click', () => this.form.clear())
		this.$save.on('click', () => this.form.save())
		this.form.on('submit', () => this.form.save())

		this.form.on('save', e => this.trigger('save', e))
		this.hud.on('show', () => this.trigger('show'))
		this.hud.on('hide', () => this.trigger('hide'))

		this.form.on('clear', () => this.hide())
		this.form.on('save', () => this.hide())
		this.hud.on('show', () => this.form.request())
		this.hud.on('hide', () => this.form.setState('idle'))

		this.hideFooter()
		this.form.on('idle', () => this.hideFooter())
		this.form.on('requesting', () => this.hideFooter())
		this.form.on('requested', () => this.showFooter())

		this.form.on('idle', () => this.setSaving(false))
		this.form.on('requesting', () => this.setSaving(false))
		this.form.on('requested', () => this.setSaving(false))
		this.form.on('saving', () => this.setSaving())

		this._monitorHeight()
	}

	destroy()
	{
		if (this.$footer)
		{
			this.$footer.remove()
			this.$footer = null
		}

		if (this.form)
		{
			this.form.destroy()
			this.form = null
		}

		if (this.hud)
		{
			this.hud.hide()
			this.hud.$hud.remove()
			this.hud.$shade.remove()
			this.hud = null
		}

		cancelAnimationFrame(this._monitor)

		this.trigger('destroy')
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

		this.trigger('show')
	}

	hide()
	{
		if (this.hud)
		{
			this.hud.hide()
		}

		this.trigger('hide')
	}

	hideFooter()
	{
		if (this.hud)
		{
			this.hud.$hud.removeClass('show-footer')
		}
	}

	showFooter()
	{
		if (this.hud)
		{
			this.hud.$hud.addClass('show-footer')
		}
	}

	setSaving(enabled = true)
	{
		this.$save.toggleClass('disabled', enabled)
		this.$spinner.toggleClass('hidden', !enabled)
	}

	isShowing()
	{
		return this.hud.showing
	}

	_monitorHeight()
	{
		const reposition = () =>
		{
			this.hud.updateSizeAndPosition()
			this._monitor = requestAnimationFrame(reposition)
		}

		reposition()
	}
}
