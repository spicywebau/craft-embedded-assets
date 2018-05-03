import $ from 'jquery'
import Craft from 'craft'

export default class Form
{
	constructor()
	{
		this.$element = null
		this.$input = null
	}

	create()
	{
		const label = Craft.t('embeddedassets', "URL")
		const instructions = Craft.t('embeddedassets', "The link to the content you'd like to embed.")

		const previewUrl = Craft.getActionUrl('embeddedassets/actions/get-preview')

		this.$element = $(`
			<div class="embedded-assets_form">
				<div class="field">
					<div class="heading">
						<label for="embeddedassets-url" class="required">${label}</label>
						<div class="instructions">
							<p>${instructions}</p>
						</div>
					</div>
					<div class="input ltr">
						<input class="text fullwidth" type="text" placeholder="http://" id="embeddedassets-url" name="url" autocomplete="off">
					</div>
				</div>
				<div class="embedded-assets_preview">
					<iframe src="${previewUrl}"></iframe>
				</div>
			</div>
		`)

		this.$input = this.$element.find('input')


	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
		this.$input = null
	}
}
