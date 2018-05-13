import $ from 'jquery'
import Craft from 'craft'
import Emitter from './Emitter'
import Preview from './Preview'
import { uniqueId, isUrl } from '../utilities'

export default class Form extends Emitter
{
	constructor(getFolderId = ()=>-1)
	{
		super()

		this._getFolderId = getFolderId

		const inputId = uniqueId()
		const bodyId = uniqueId()

		const formAction = Craft.getActionUrl('embeddedassets/actions/save')

		this.$element = $(`
			<form class="embedded-assets_form" action="${formAction}" method="post">
				<div class="embedded-assets_form_field">
					<label for="${inputId}">URL</label>
					<input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off" spellcheck="false">
				</div>
				<div id="${bodyId}" class="embedded-assets_form_body">
					<div class="spinner"></div>
				</div>
			</form>
		`)

		this.$input = this.$element.find(`#${inputId}`)
		this.$body = this.$element.find(`#${bodyId}`)

		this.preview = new Preview()
		this.$body.prepend(this.preview.$element)

		this.preview.on('load', e =>
		{
			if (e.url === this._url && this._state === 'requesting')
			{
				this.setState('requested')
			}
		})

		this.preview.on('timeout', e =>
		{
			if (e.url === this._url && this._state === 'requesting')
			{
				Craft.cp.displayError(Craft.t('embeddedassets', `Could not retrieve embed information.`))

				this.setState('idle')
			}
		})

		this.preview.on('resize', e => this.$body.css('height', e.height + 'px'))

		this.$element.on('submit', e =>
		{
			e.preventDefault()

			const isSameUrl = this._url === this.$input.val()

			if (this._state === 'idle' || (this._state !== 'saving' && !isSameUrl))
			{
				this.request()
			}
			else if (this._state === 'requested' && isSameUrl)
			{
				this.save()
			}
		})

		this.$input.on('change blur', () => this.request())

		this.$input.on('paste', e =>
		{
			const clipboardData = e.clipboardData || e.originalEvent.clipboardData || window.clipboardData
			const url = clipboardData.getData('text')

			this.request(url)
		})

		this.setState('idle')
	}

	destroy()
	{
		this.preview.destroy()

		this.$element.remove()
		this.$element = null
		this.$input = null
		this.$body = null

		this.trigger('destroy')
	}

	request(url = this.$input.val())
	{
		if (this._state !== 'saving' && this._url !== url)
		{
			this._url = url

			if (isUrl(url))
			{
				this.setState('requesting')
				this.preview.setUrl(url)
			}
			else
			{
				this.setState('idle')
			}
		}
	}

	focus()
	{
		this.$input[0].select()
		this.$input[0].focus()
	}

	clear()
	{
		this.$input.val('')

		this.trigger('clear')
		this.setState('idle')
	}

	save(url = this.$input.val(), folderId = this._getFolderId())
	{
		Craft.queueActionRequest('embeddedassets/actions/save', { url, folderId }, (response, status) =>
		{
			if (this._state === 'saving' && status === 'success' && response.success)
			{
				this.clear()
				this.trigger('save', response.payload)
			}
			else
			{
				if (response && response.error)
				{
					Craft.cp.displayError(response.error)
				}

				this.setState('requested')
			}
		})

		this.setState('saving')
	}

	setState(state)
	{
		this._state = state
		this.$element.attr('data-state', state)

		switch (state)
		{
			case 'idle':
			{
				this._url = ''
				this.trigger('idle')
			}
			break
			case 'requesting':
			{
				this.trigger('requesting')
			}
			break
			case 'requested':
			{
				this.trigger('requested')
			}
			break
			case 'saving':
			{
				this.trigger('saving')
			}
			break
		}
	}
}
