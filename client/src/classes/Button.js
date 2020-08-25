import $ from 'jquery'
import Craft from 'craft'

export default class Button {
  constructor (label) {
    this.$element = $('<div class="embedded-assets_button btn" data-icon="globe"></div>')
    this.setLabel(label)
  }

  destroy () {
    this.$element.remove()
    this.$element = null
  }

  setLabel (label) {
    this.$element.text(Craft.t('embeddedassets', label))
  }

  show () {
    this.$element.show()
  }

  hide () {
    this.$element.hide()
  }

  setActive (flag = true) {
    this.$element.toggleClass('active', flag)
  }
}
