import './preview.scss'

window.EmbeddedAssetsPreview = {

	addCallback(callbackName)
	{
		window.addEventListener('load', () =>
		{
			const callback = window.parent ? window.parent[callbackName] : false

			if (typeof callback === 'function')
			{
				requestAnimationFrame(callback)
			}
		})
	},

	applyRatio(codeEl)
	{
		const iframeEl = Array.from(codeEl.children).find((el) => el.tagName.toLowerCase() === 'iframe')

		if (iframeEl)
		{
			codeEl.classList.add('is-ratio')

			const width = iframeEl.getAttribute('width')|0
			const height = iframeEl.getAttribute('height')|0

			if (width && height)
			{
				codeEl.style.paddingTop = ((height / width) * 100) + '%'
			}
		}
	},
}
