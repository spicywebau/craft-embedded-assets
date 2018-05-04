import $ from 'jquery'
import { uniqueId } from './utilities'

export default class Preview
{
	constructor(info = {}, useCode = false)
	{
		let innerElement

		if (useCode && info.code)
		{
			innerElement = `<div class="embedded-assets_preview_code">${info.code}</div>`
		}
		else
		{
			const imageElement = info.image ?
				`<img src="${info.image.url}" width="${info.image.width}" height="${info.image.height}">` :
				`<div class="embedded-assets_preview_placeholder"></div>`

			const titleElement = info.title ?
				'<h1>${info.title</h1>' :
				'<h1><em>No title</em></h1>'

			innerElement =
				`<div class="embedded-assets_preview_content">
					${imageElement}
					${titleElement}
				</div>`
		}

		this.$element = $(`<div class="embedded-assets_preview">${innerElement}</div>`)
	}

	destroy()
	{
		this.$element.remove()
		this.$element = null
	}
}
