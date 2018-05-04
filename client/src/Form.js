import $ from 'jquery'
import Craft from 'craft'
import Preview from './Preview'
import { uniqueId } from './utilities'

const STATE_IDLE = 'STATE_IDLE'
const STATE_REQUESTING = 'STATE_REQUESTING'
const STATE_REQUESTED = 'STATE_REQUESTED'
const STATE_SAVING = 'STATE_SAVING'

export default class Form
{
	constructor()
	{
		const inputId = uniqueId()
		const previewId = uniqueId()

		const cancelLabel = Craft.t('app', "Cancel")
		const saveLabel = Craft.t('app', "Save")

		this.$element = $(`
			<div class="embedded-assets_form">
				<div class="embedded-assets_form_field">
					<label for="${inputId}">URL</label>
					<input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off">
				</div>
				<div id="${previewId}" class="embedded-assets_form_preview">
					<div class="spinner"></div>
				</div>
			</div>
			<div class="hud-footer">
				<div class="buttons right">
					<div class="btn">${cancelLabel}</div>
					<input class="btn submit" type="submit" value="${saveLabel}">
					<div class="spinner hidden"></div>
				</div>
			</div>
		`)

		this.$input = this.$element.find(`#${inputId}`)
		this.$preview = this.$element.find(`#${previewId}`)

		this.$input.on('change blur', () => this.submit())
		this.$input.on('paste', (e) =>
		{
			const clipboardData = e.clipboardData || e.originalEvent.clipboardData || window.clipboardData
			const url = clipboardData.getData('text')
			this.submit(url)
		})

		this._monitorPreviewHeight()
		this._setState(STATE_IDLE)
	}

	destroy()
	{
		if (this._preview)
		{
			this._preview.destroy()
			this._preview = null
		}

		this.$element.remove()
		this.$element = null
		this.$input = null
		this.$preview = null

		cancelAnimationFrame(this._monitorPreviewHeightFrame)
	}

	submit(url = null)
	{
		url = url || this.$input.val()

		if (this._url !== url)
		{
			this._url = url

			this._setState(STATE_REQUESTING)

			this._requestUrl(url)
				.then(({ info, isSafe }) =>
				{
					this._setPreview(info, isSafe)

					this._setState(STATE_REQUESTED)
				})
				.catch(errors =>
				{
					errors.forEach(error => Craft.cp.displayError(error))

					this._setState(STATE_IDLE)
				})
		}
	}

	_requestUrl(url)
	{
		return new Promise((resolve, reject) =>
		{
			Craft.queueActionRequest('embeddedassets/actions/request-url', { url }, (response, status) =>
			{
				if (status === 'success' && response.success && response.payload)
				{
					resolve(response.payload)
				}
				else
				{
					const errors = response && Array.isArray(response.errors) ? response.errors : []

					if (errors.length === 0)
					{
						errors.push("No embeddable information found for this URL.")
					}

					reject(errors)
				}
			})
		})
	}

	_monitorPreviewHeight()
	{
		const setHeight = () =>
		{
			if (this._preview)
			{
				this.$preview.css('height', this._preview.$element.height() + 'px')
			}

			this._monitorPreviewHeightFrame = requestAnimationFrame(setHeight)
		}

		setHeight()
	}

	_setPreview(info, isSafe = false)
	{
		if (this._preview)
		{
			this._preview.destroy()
			this._preview = null
		}

		if (info)
		{
			const preview = new Preview(info, isSafe)

			this.$preview.append(preview.$element)
			this._preview = preview
		}
	}

	_setState(state)
	{
		switch (state)
		{
			default: // STATE_IDLE
			{
				this.$element.attr('data-state', 'idle')
			}
			break;
			case STATE_REQUESTING:
			{
				this.$element.attr('data-state', 'requesting')
			}
			break
			case STATE_REQUESTED:
			{
				this.$element.attr('data-state', 'requested')
			}
			break
			case STATE_SAVING:
			{
				this.$element.attr('data-state', 'saving')
			}
			break
		}
	}
}
