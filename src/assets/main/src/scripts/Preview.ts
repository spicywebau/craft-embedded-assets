import * as $ from 'jquery'
import Emitter from './Emitter'
import { uniqueId } from './utilities'

interface RequestParameters {
  assetId?: string
  callback?: string
  showContent: 0 | 1
  url?: string
}

interface RequestSettings {
  assetId?: string
  callback?: string
  showContent?: boolean
  url?: string
}

export default class Preview extends Emitter {
  public $element: JQuery | null
  public $iframe: JQuery<HTMLIFrameElement>
  private _$warningTrigger?: JQuery | null
  private _height: number
  private _heightMonitor: number
  private _requestTimeout: NodeJS.Timeout
  private _warningHud?: GarnishHUD | null

  constructor () {
    super()

    if (typeof window.EmbeddedAssetsPreviewMap === 'undefined') {
      window.EmbeddedAssetsPreviewMap = new Map()
    }

    const iframeId = uniqueId()

    this.$element = $(`
      <div class="embedded-assets_preview">
        <iframe id="${iframeId}" src="about:blank"></iframe>
      </div>
    `)

    this.$iframe = this.$element.find(`#${iframeId}`) as JQuery<HTMLIFrameElement>

    this._setupHeightMonitor()
  }

  public destroy (): void {
    this.$element?.remove()
    this.$element = null

    if (typeof this._$warningTrigger !== 'undefined' && this._$warningTrigger !== null) {
      this._$warningTrigger.remove()
      this._$warningTrigger = null
    }

    if (typeof this._warningHud !== 'undefined' && this._warningHud !== null) {
      this._warningHud.hide()
      this._warningHud.$hud.remove()
      this._warningHud.$shade.remove()
      this._warningHud = null
    }

    window.cancelAnimationFrame(this._heightMonitor)
    clearTimeout(this._requestTimeout)

    this.trigger('destroy')
  }

  public getWindow (): Window | null {
    return this.$iframe[0].contentWindow
  }

  public getDocument (): Document | null {
    return this.getWindow()?.document ?? null
  }

  public getBody (): HTMLElement | null {
    return this.getDocument()?.body ?? null
  }

  public showWarning (): void {
    const $previewWindow = $(this.getWindow() as Window)
    const $previewDocument = $(this.getDocument() as Document)
    const $warning = $previewDocument.find('#warning')

    if ($warning.length > 0) {
      const { top: frameTop, left: frameLeft } = this.$iframe.offset() as JQueryCoordinates
      const frameScroll = $previewWindow.scrollTop() as number

      const { top: iconTop, left: iconLeft } = $warning.offset() as JQueryCoordinates

      const top = frameTop - frameScroll + iconTop
      const left = frameLeft + iconLeft
      const width = $warning.outerWidth() as number
      const height = $warning.outerHeight() as number

      if (typeof this._$warningTrigger === 'undefined' || this._$warningTrigger === null) {
        this._$warningTrigger = $('<div>').css({
          position: 'absolute',
          display: 'none'
        })

        Garnish.$bod.append(this._$warningTrigger)
      }

      this._$warningTrigger.css({
        display: 'block',
        top: `${top}px`,
        left: `${left}px`,
        width: `${width}px`,
        height: `${height}px`
      })

      if (typeof this._warningHud === 'undefined' || this._warningHud === null) {
        const untrustedSource = Craft.t('embeddedassets', 'This information is coming from an untrusted source.')
        const securityMeasure = window.EmbeddedAssets.preventNonWhitelistedUploads
          ? Craft.t('embeddedassets', 'This embedded asset cannot be saved.')
          : Craft.t('embeddedassets', 'As a security measure embed codes will not be shown.')
        const $message = $(`
          <p><strong>${untrustedSource}</strong></p>
          <p>${securityMeasure}</p>
        `)

        this._warningHud = new Garnish.HUD(this._$warningTrigger, $message, {
          hudClass: 'hud info-hud',
          closeOtherHUDs: false,
          onHide: () => this._$warningTrigger?.css('display', 'none')
        })
      } else {
        this._warningHud.show()
      }
    }
  }

  public request (settings?: RequestSettings, timeout: number = 15000): void {
    const reqSettings = Object.assign({
      showContent: true,
      callback: uniqueId('embeddedassets')
    }, settings ?? {}) as RequestSettings

    const previewWindow = this.getWindow()

    if (previewWindow !== null) {
      clearTimeout(this._requestTimeout)

      const showPreview = Boolean(reqSettings.url ?? reqSettings.assetId)
      let previewUrl = 'about:blank'

      if (showPreview) {
        const complete: (trigger: string) => void = (trigger) => {
          clearTimeout(this._requestTimeout)

          if (window.EmbeddedAssetsPreviewMap?.has(reqSettings.callback as string) ?? false) {
            window.EmbeddedAssetsPreviewMap?.delete(reqSettings.callback as string)
          }

          this.trigger(trigger, parameters)
        }

        const preventNonWhitelistedUploads = window.EmbeddedAssets.preventNonWhitelistedUploads
        window.EmbeddedAssetsPreviewMap?.set(
          reqSettings.callback as string,
          () => complete(this._setupWarning() && preventNonWhitelistedUploads ? 'blacklisted' : 'load')
        )

        this._requestTimeout = setTimeout(() => complete('timeout'), timeout)

        const parameters: RequestParameters = {
          showContent: (reqSettings.showContent ?? true) ? 1 : 0,
          callback: reqSettings.callback
        }

        if (typeof reqSettings.url !== 'undefined') {
          parameters.url = reqSettings.url
        } else if (typeof reqSettings.assetId !== 'undefined') {
          parameters.assetId = reqSettings.assetId
        }

        previewUrl = Craft.getActionUrl('embeddedassets/actions/preview', {
          ...parameters,
          url: parameters?.url ?? null
        })
      }

      previewWindow.location.replace(previewUrl)
    }
  }

  private _setupHeightMonitor (): void {
    this._height = 0

    const monitorHeight: () => void = () => {
      const $previewBody = $(this.getBody() ?? $())
      const height = $previewBody.height() ?? 0

      if (this._height !== height) {
        this.trigger('resize', { prevHeight: this._height, height })
        this._height = height
      }

      this._heightMonitor = window.requestAnimationFrame(monitorHeight)
    }

    monitorHeight()
  }

  private _setupWarning (): boolean {
    const $previewDocument = $(this.getDocument() ?? $())
    const $warning = $previewDocument.find('#warning')
    const hasWarning = $warning.length > 0

    if (hasWarning) {
      // Just in case
      $warning.off('.embeddedassets')
      $warning.on('click.embeddedassets', () => this.showWarning())
    }

    return hasWarning
  }
}
