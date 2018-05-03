import $ from 'jquery'
import Craft from 'craft'
import { uniqueId } from './utilities'

export default class Form
{
	constructor()
	{
		this.$element = null
		this.$input = null
		this.$iframe = null
	}

	create()
	{
		const inputId = uniqueId()
		const iframeId = uniqueId()

		const cancelLabel = Craft.t('app', "Cancel")
		const saveLabel = Craft.t('app', "Save")

		this.$element = $(`
			<div class="embedded-assets_form">
				<div class="embedded-assets_field">
					<label for="${inputId}">URL</label>
					<input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off">
				</div>
				<div class="embedded-assets_preview">
					<iframe id="${iframeId}" src="about:blank"></iframe>
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
		this.$iframe = this.$element.find(`#${iframeId}`)

	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
		this.$input = null
		this.$iframe = null
	}
}
