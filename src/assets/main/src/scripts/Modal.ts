import * as $ from 'jquery'
import Emitter from './Emitter'
import Form from './Form'
import { uniqueId } from './utilities'

export default class Modal extends Emitter {
  public $cancel: JQuery
  public $footer: JQuery | null
  public $save: JQuery
  public $spinner: JQuery
  public form: Form | null
  public hud: GarnishHUD | null
  private readonly _monitor: number

  constructor (private readonly _getActionTarget: Function = () => {}) {
    super()

    this.form = null
    this.hud = null
  }

  public create ($target: JQuery, settings = {}): void {
    settings = Object.assign({
      hudClass: 'hud embedded-assets_hud',
      mainClass: 'embedded-assets_hud_main',
      minBodyWidth: 400
    }, settings)

    const cancelId = uniqueId()
    const saveId = uniqueId()
    const spinnerId = uniqueId()
    const cancelLabel = Craft.t('app', 'Cancel')
    const saveLabel = Craft.t('app', 'Save')

    this.$footer = $(`
      <div class="hud-footer embedded-assets_hud_footer">
        <div class="buttons right">
          <div id="${cancelId}" class="btn">${cancelLabel}</div>
          <div id="${saveId}" class="btn submit">${saveLabel}</div>
          <div id="${spinnerId}" class="spinner hidden"></div>
        </div>
      </div>
    `)

    this.$cancel = this.$footer.find(`#${cancelId}`)
    this.$save = this.$footer.find(`#${saveId}`)
    this.$spinner = this.$footer.find(`#${spinnerId}`)

    this.form = new Form(this._getActionTarget)
    this.hud = new Garnish.HUD($target, this.form.$element?.add(this.$footer) as JQuery, settings)

    this.trigger('create')

    this.$save.on('click', (e) => this.$save.hasClass('disabled') && e.stopImmediatePropagation())

    this.$cancel.on('click', () => this.form?.clear())
    this.$save.on('click', () => this.form?.save())
    this.form.on('submit', () => this.form?.save())

    this.form.on('save', (e: any) => this.trigger('save', e))
    this.hud.on('show', () => this.trigger('show'))
    this.hud.on('hide', () => this.trigger('hide'))

    this.form.on('clear', () => this.hide())
    this.form.on('save', () => this.hide())
    this.hud.on('show', () => this.form?.request())
    this.hud.on('show', () => this.form?.focus())
    this.hud.on('hide', () => this.form?.setState('idle'))

    this.hideFooter()
    this.form.on('idle', () => this.hideFooter())
    this.form.on('requesting', () => this.hideFooter())
    this.form.on('requested', () => this.showFooter())
    this.form.on('blacklisted', () => this.hideFooter())

    this.form.on('idle', () => this.setSaving(false))
    this.form.on('requesting', () => this.setSaving(false))
    this.form.on('requested', () => this.setSaving(false))
    this.form.on('blacklisted', () => this.setSaving(false))
    this.form.on('saving', () => this.setSaving())
    this.form.on('resize', () => this.hud?.updateSizeAndPosition())
  }

  public destroy (): void {
    if (this.$footer !== null) {
      this.$footer.remove()
      this.$footer = null
    }

    if (this.form !== null) {
      this.form.destroy()
      this.form = null
    }

    if (this.hud !== null) {
      this.hud.hide()
      this.hud.$hud.remove()
      this.hud.$shade.remove()
      this.hud = null
    }

    window.cancelAnimationFrame(this._monitor)

    this.trigger('destroy')
  }

  public show ($target: JQuery, settings = {}, replace: any): void {
    if (this.hud !== null) {
      this.hud.setSettings(settings)
      this.hud.$trigger = $($target)

      if (this.hud.showing) {
        this.hud.queueUpdateSizeAndPosition()
      } else {
        this.hud.show()
      }
    } else {
      this.create($target, settings)
      // `this.create()` creates the form
      this.form?.focus()
    }

    this.trigger('show')
  }

  public hide (): void {
    this.hud?.hide()
    this.trigger('hide')
  }

  public hideFooter (): void {
    this.hud?.$hud.removeClass('show-footer')
  }

  public showFooter (): void {
    this.hud?.$hud.addClass('show-footer')
  }

  public setSaving (enabled = true): void {
    this.$save.toggleClass('disabled', enabled)
    this.$spinner.toggleClass('hidden', !enabled)
  }
}
