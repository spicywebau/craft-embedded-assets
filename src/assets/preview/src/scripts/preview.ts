import '../styles/preview.scss'

declare global {
  interface Window {
    EmbeddedAssetsPreview: {
      addCallback: (callbackName: string) => void
      applyRatio: (codeEl: HTMLElement) => void
    }
  }
}

window.EmbeddedAssetsPreview = {

  addCallback (callbackName: string) {
    window.addEventListener('load', () => {
      const callback = window.parent?.EmbeddedAssetsPreviewMap?.get(callbackName) ?? false

      if (typeof callback === 'function') {
        window.requestAnimationFrame(callback)
      }
    })
  },

  applyRatio (codeEl: HTMLElement) {
    const iframeEl = Array.from(codeEl.children).find((el) => el.tagName.toLowerCase() === 'iframe')

    if (typeof iframeEl !== 'undefined') {
      const iframe = iframeEl as HTMLIFrameElement
      const width = iframe.offsetWidth
      const height = iframe.offsetHeight
      const paddingTop = height / width * 100

      codeEl.classList.add('is-ratio')
      codeEl.style.paddingTop = `${paddingTop}%`
    } else {
      codeEl.classList.remove('is-ratio')
      codeEl.style.paddingTop = ''
    }
  }
}
