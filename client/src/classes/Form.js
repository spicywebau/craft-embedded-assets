import $ from 'jquery'
import Craft from 'craft'
import Emitter from './Emitter'
import { uniqueId, isUrl } from '../utilities'

export default class Form extends Emitter
{
	constructor(getFolderId = ()=>-1)
	{
		super()

		this._getFolderId = getFolderId

		const inputId = uniqueId()
		const bodyId = uniqueId()
		const previewId = uniqueId()

		const formAction = Craft.getActionUrl('embeddedassets/actions/save')

		this.$element = $(`
			<form class="embedded-assets_form" action="${formAction}" method="post">
				<div class="embedded-assets_form_field">
					<label for="${inputId}">URL</label>
					<input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off">
				</div>
				<div id="${bodyId}" class="embedded-assets_form_body">
					<iframe id="${previewId}" src="about:blank"></iframe>
					<div class="spinner"></div>
				</div>
			</form>
		`)

		this.$input = this.$element.find(`#${inputId}`)
		this.$body = this.$element.find(`#${bodyId}`)
		this.$preview = this.$element.find(`#${previewId}`)

		this.$element.on('submit', (e) =>
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

		this.$input.on('paste', (e) =>
		{
			const clipboardData = e.clipboardData || e.originalEvent.clipboardData || window.clipboardData
			const url = clipboardData.getData('text')

			this.request(url)
		})

		this._callbackName = uniqueId('embeddedAssets_')
		window[this._callbackName] = () =>
		{
			const isPreviewUrl = Boolean(this._getCurrentPreviewUrl())

			if (isPreviewUrl)
			{
				this.setState('requested')
			}
		}

		this._monitorPreviewHeight()
		this.setState('idle')
	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
		this.$input = null
		this.$body = null
		this.$preview = null

		cancelAnimationFrame(this._previewMonitor)

		delete window[this._callbackName]

		this.trigger('destroy')
	}

	request(url = this.$input.val())
	{
		if (this._url !== url)
		{
			this._url = url

			if (isUrl(url))
			{
				this._setPreview(url)
				this.setState('requesting')
			}
			else
			{
				this.setState('idle')
			}
		}
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
			if (status === 'success' && response.success)
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

	_setPreview(url)
	{
		const callback = this._callbackName
		const previewUrl = url ? Craft.getActionUrl('embeddedassets/actions/preview', { url, callback }) : 'about:blank'
		const previewWindow = this.$preview[0].contentWindow

		if (previewWindow)
		{
			previewWindow.location.replace(previewUrl)
		}
	}

	_getCurrentPreviewUrl()
	{
		const previewWindow = this.$preview[0].contentWindow
		const url = previewWindow ? previewWindow.location.href : ''

		return url.indexOf('embeddedassets') > 0 ? url : false
	}

	_monitorPreviewHeight()
	{
		const setHeight = () =>
		{
			const isPreviewUrl = Boolean(this._getCurrentPreviewUrl())

			if (isPreviewUrl && this.$preview[0].contentDocument)
			{
				const $previewBody = $(this.$preview[0].contentDocument.body)
				this.$body.css('height', $previewBody.height() + 'px')
			}
			else
			{
				this.$body.css('height', '')
			}

			this._previewMonitor = requestAnimationFrame(setHeight)
		}

		setHeight()
	}
}
