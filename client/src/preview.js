import './preview.scss'

window.EmbeddedAssetsPreview = {

	setCallback(callbackName)
	{
		window.onload = () => requestAnimationFrame(window.parent ? window.parent[callbackName] : () => {})
	}
}
