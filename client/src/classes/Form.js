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
					<input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off" spellcheck="false">
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
		if (this._state !== 'saving' && this._url !== url)
		{
			this._url = url

			if (isUrl(url))
			{
				const isRequesting = () => this._url === url && this._state === 'requesting'
				this.setState('requesting')
				this._setPreview(url)
					.then(() =>
					{
						if (isRequesting())
						{
							this.setState('requested')
						}

						this._setupWarning()
					})
					.catch(() =>
					{
						if (isRequesting())
						{
							Craft.cp.displayError(Craft.t('embeddedassets', `Could not retrieve embed information.`))
							this.setState('idle')
						}
					})
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

	_setPreview(url, timeout = 15000)
	{
		return new Promise((resolve, reject) =>
		{
			const previewWindow = this.$preview[0].contentWindow

			if (previewWindow)
			{
				const isPreviewUrl = Boolean(url)
				let previewUrl = 'about:blank'

				if (isPreviewUrl)
				{
					const callback = uniqueId('embeddedAssets_')
					const cleanup = () => delete window[callback]

					previewUrl = Craft.getActionUrl('embeddedassets/actions/preview', { url, callback })

					// On load
					window[callback] = () => { resolve(); cleanup() }

					// On timeout
					setTimeout(() => { reject(); cleanup() }, timeout)
				}

				previewWindow.location.replace(previewUrl)

				if (!isPreviewUrl)
				{
					resolve()
				}
			}
			else
			{
				reject()
			}
		})
	}

	_getCurrentPreviewUrl()
	{
		const previewWindow = this.$preview[0].contentWindow
		const url = previewWindow ? previewWindow.location.href : ''

		return url.indexOf('embeddedassets') > 0 ? url : false
	}

	_monitorPreviewHeight()
	{
		let previousHeight = 0

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

			const nextHeight = this.$body.height()

			if (previousHeight !== nextHeight)
			{
				this.trigger('resize', {
					previousHeight,
					nextHeight,
				})

				previousHeight = nextHeight
			}

			this._previewMonitor = requestAnimationFrame(setHeight)
		}

		setHeight()
	}

	_setupWarning()
	{
		const isPreviewUrl = Boolean(this._getCurrentPreviewUrl())

		if (isPreviewUrl && this.$preview[0].contentDocument)
		{
			const $previewWindow = $(this.$preview[0].contentWindow)
			const $previewHtml = $(this.$preview[0].contentDocument)
			const $previewWarning = $previewHtml.find('#warning')

			// Just in case
			$previewWarning.off('.embeddedassets')

			$previewWarning.on('click.embeddedassets', () =>
			{
				const { top: frameTop, left: frameLeft } = this.$preview.offset()
				const frameScroll = $previewWindow.scrollTop()

				const { top: iconTop, left: iconLeft } = $previewWarning.offset()

				const top = frameTop - frameScroll + iconTop
				const left = frameLeft + iconLeft
				const width = $previewWarning.outerWidth()
				const height = $previewWarning.outerHeight()

				if (!this._$warningTrigger)
				{
					this._$warningTrigger = $('<div>').css({
						position: 'absolute',
						display: 'none',
					})

					Garnish.$bod.append(this._$warningTrigger)
				}

				this._$warningTrigger.css({
					display: 'block',
					top: top + 'px',
					left: left + 'px',
					width: width + 'px',
					height: height + 'px',
				})

				if (!this._warningHud)
				{
					const untrustedSource = Craft.t('embeddedassets', "This information is coming from an untrusted source.")
					const securityMeasure = Craft.t('embeddedassets', "As a security measure embed codes will not be shown.")
					const $message = $(`
						<p><strong>${untrustedSource}</strong></p>
						<p>${securityMeasure}</p>
					`)

					this._warningHud = new Garnish.HUD(this._$warningTrigger, $message, {
						hudClass: 'hud info-hud',
						closeOtherHUDs: false,
						onHide: () => this._$warningTrigger.css('display', 'none')
					})
				}
				else
				{
					this._warningHud.show()
				}
			})
		}
	}
}
