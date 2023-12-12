import Button from './Button'
import Emitter from './Emitter'
import Modal from './Modal'

export default class EmbeddedAssets extends Emitter {
  public modal: Modal | null
  public buttons: Button[]
  private _replaceAssetId: string
  private _currentGetActionTarget: Function
  private _preventNonWhitelistedUploads?: boolean

  constructor () {
    super()

    this._currentGetActionTarget = () => -1

    this.modal = new Modal(() => this._getActionTarget())
    this.buttons = []

    this.modal.on('hide', () => this.buttons.forEach(b => b.setActive(false)))
    this.modal.on('save', (e: Event) => this.trigger('save', e))
  }

  public get preventNonWhitelistedUploads (): boolean {
    return this._preventNonWhitelistedUploads ?? false
  }

  public set preventNonWhitelistedUploads (prevent: boolean) {
    // Don't allow it to be reset
    if (typeof this._preventNonWhitelistedUploads === 'undefined') {
      this._preventNonWhitelistedUploads = prevent
    }
  }

  public destroy (): void {
    if (this.modal !== null) {
      this.modal.destroy()
      this.modal = null

      this.trigger('destroy')
    }
  }

  public setReplaceAssetId (id: string): void {
    this._replaceAssetId = id
  }

  public addButton (
    button: Button,
    orientations: string[] = ['bottom', 'top', 'left', 'right'],
    getActionTarget: Function = () => {},
    replace: boolean = false
  ): void {
    this.buttons.push(button)

    button.$element?.on('click', () => {
      if (this.modal !== null) {
        this._currentGetActionTarget = getActionTarget

        this.buttons.forEach((b) => b.setActive(b === button))
        this.modal.show(button.$element as JQuery, { orientations }, replace)
        this.modal.form?.setReplace(replace, this._replaceAssetId)
      }
    })

    this.trigger('addButton', { button })
  }

  public removeButton (button: Button): void {
    this.buttons = this.buttons.filter(b => b !== button)

    this.trigger('removeButton', { button })
  }

  private _getActionTarget (): void {
    return this._currentGetActionTarget()
  }
}
