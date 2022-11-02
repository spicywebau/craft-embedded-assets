import * as $ from 'jquery'

export default class Button {
  public $element: JQuery | null

  constructor (label: string) {
    this.$element = $('<div class="embedded-assets_button btn" data-icon="globe"></div>')
    this.setLabel(label)
  }

  public destroy (): void {
    this.$element?.remove()
    this.$element = null
  }

  public setLabel (label: string): void {
    this.$element?.text(Craft.t('embeddedassets', label))
  }

  public show (): void {
    this.$element?.show()
  }

  public hide (): void {
    this.$element?.hide()
  }

  public setActive (flag: boolean = true): void {
    this.$element?.toggleClass('active', flag)
  }
}
