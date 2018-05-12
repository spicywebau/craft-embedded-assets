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
}
