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
			const width = iframeEl.offsetWidth
			const height = iframeEl.offsetHeight

			codeEl.classList.add('is-ratio')
			codeEl.style.paddingTop = ((height / width) * 100) + '%'
		}
		else
		{
			codeEl.classList.remove('is-ratio')
			codeEl.style.paddingTop = ''
		}
	},
}
