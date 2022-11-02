import * as $ from 'jquery'
import Emitter from './Emitter'
import Preview from './Preview'
import { PreviewResizeEvent } from './events'
import { PreviewResponse } from './responses'
import { uniqueId, isUrl } from './utilities'

export default class Form extends Emitter {
  public $body: JQuery | null
  public $element: JQuery | null
  public $input: JQuery | null
  public preview: Preview
  private _height: number
  private _heightMonitor: number
  private _replace: boolean
  private _replaceAssetId: string
  private _state: string = 'idle'
  private _url: string = ''

  constructor (private readonly _getActionTarget: Function = () => {}) {
    super()

    const inputId = uniqueId()
    const bodyId = uniqueId()

    const formAction = Craft.getActionUrl('embeddedassets/actions/save')

    this.$element = $(`
      <form class="embedded-assets_form" action="${formAction}" method="post">
        <div class="embedded-assets_form_field">
          <label for="${inputId}">URL</label>
          <input type="text" placeholder="http://" id="${inputId}" name="url" autocomplete="off" spellcheck="false">
        </div>
        <div id="${bodyId}" class="embedded-assets_form_body">
          <div class="spinner"></div>
        </div>
      </form>
    `)

    this.$input = this.$element.find(`#${inputId}`)
    this.$body = this.$element.find(`#${bodyId}`)

    this.preview = new Preview()
    this.$body.prepend(this.preview.$element as JQuery)

    this.preview.on('load', (e: any) => {
      if (e.url === this._url && this._state === 'requesting') {
        this.setState('requested')
      }
    })

    this.preview.on('blacklisted', (e: any) => {
      if (e.url === this._url && this._state === 'requesting') {
        this.setState('blacklisted')
      }
    })

    this.preview.on('timeout', (e: any) => {
      if (e.url === this._url && this._state === 'requesting') {
        Craft.cp.displayError(Craft.t('embeddedassets', 'Could not retrieve embed information.'))

        this.setState('idle')
      }
    })

    this.preview.on('resize', (e: PreviewResizeEvent) => this.$body?.css('height', `${e.height}px`))

    this.$element.on('submit', (e) => {
      e.preventDefault()

      const isSameUrl = this._url === this.$input?.val()

      if (this._state === 'idle' || (this._state !== 'saving' && !isSameUrl)) {
        this.request()
      } else if (this._state === 'requested' && isSameUrl) {
        this.save()
      }
    })

    this.$input.on('change blur', () => this.request())

    this.$input.on('paste', (e: JQueryEventObject) => {
      const clipboardData = (e.originalEvent as ClipboardEvent).clipboardData
      const url = clipboardData?.getData('text')

      this.request(url)
    })

    this._setupHeightMonitor()
  }

  public destroy (): void {
    this.preview.destroy()

    this.$element?.remove()
    this.$element = null
    this.$input = null
    this.$body = null

    window.cancelAnimationFrame(this._heightMonitor)

    this.trigger('destroy')
  }

  public request (url = this.$input?.val() as string): void {
    if (this._state !== 'saving' && this._url !== url) {
      this._url = url

      if (isUrl(url)) {
        this.setState('requesting')
        this.preview.request({ url })
      } else {
        this.setState('idle')
      }
    }
  }

  public focus (): void {
    if (this.$input !== null) {
      const $input = this.$input as JQuery<HTMLInputElement>
      $input[0].select()
      $input[0].focus()
    }
  }

  public clear (): void {
    this.$input?.val('')
    this.trigger('clear')
    this.setState('idle')
  }

  public setReplace (replace: boolean, id: string): void {
    this._replace = replace
    this._replaceAssetId = id
  }

  public save (url = this.$input?.val() as string, actionTarget = this._getActionTarget()): void {
    const data = {
      ...actionTarget,
      url,
      assetId: this._replaceAssetId
    }

    Craft.queue.push(async () => await new Promise((resolve, reject) => {
      Craft.sendActionRequest('POST', `embeddedassets/actions/${(this._replace ? 'replace' : 'save')}`, { data })
        .then((response: PreviewResponse) => {
          if (this._state === 'saving' && typeof response.data?.success !== 'undefined') {
            this.clear()
            this.trigger('save', response.data.payload)
            resolve(undefined)
          } else {
            if (typeof response.data?.error !== 'undefined') {
              Craft.cp.displayError(response.data.error)
            }

            this.setState('requested')
            reject(new Error(response.data?.error))
          }
        })
        .catch(reject)
    }))

    this.setState('saving')
  }

  public setState (state: string): void {
    this._state = state
    this.$element?.attr('data-state', state)

    switch (state) {
      case 'idle':
        this._url = ''
        this.trigger('idle')
        break
      case 'requesting':
        this.trigger('requesting')
        break
      case 'requested':
        this.trigger('requested')
        break
      case 'blacklisted':
        this.trigger('blacklisted')
        break
      case 'saving':
        this.trigger('saving')
        break
    }
  }

  private _setupHeightMonitor (): void {
    this._height = 0

    const monitorHeight: () => void = () => {
      const height = this.$element?.height() ?? 0

      if (this._height !== height) {
        this.trigger('resize', { prevHeight: this._height, height })
        this._height = height
      }

      this._heightMonitor = window.requestAnimationFrame(monitorHeight)
    }

    monitorHeight()
  }
}
