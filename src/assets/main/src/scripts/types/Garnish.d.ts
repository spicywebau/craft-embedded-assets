/**
 * An instance of Garnish.
 */
declare const Garnish: {
  $bod: JQuery
  HUD: new(trigger: JQuery, bodyContents: JQuery, settings: Object) => GarnishHUD
}

declare interface GarnishHUD {
  $hud: JQuery
  $shade: JQuery
  $trigger: JQuery
  showing: boolean
  hide: () => void
  off: (events: string, handler: Function) => void
  on: (events: string, handler: Function) => void
  queueUpdateSizeAndPosition: () => void
  setSettings: (settings: Object, defaults?: Object) => void
  show: () => void
  updateSizeAndPosition: () => void
}
