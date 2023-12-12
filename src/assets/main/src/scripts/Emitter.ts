export default class Emitter {
  private readonly _events: Map<string, Function[]>

  constructor () {
    this._events = new Map()
  }

  public on (type: string, callback: Function): void {
    if (!this._events.has(type)) {
      this._events.set(type, [])
    }

    this._events.get(type)?.push(callback)
  }

  public off (type: string, callback?: Function): void {
    if (this._events.size > 0) {
      if (typeof callback !== 'undefined' && this._events.has(type)) {
        const newList = this._events.get(type)?.filter((fn) => fn !== callback) as Function[]

        if (newList.length > 0) {
          this._events.set(type, newList)
        } else {
          this._events.delete(type)
        }
      } else {
        this._events.delete(type)
      }
    }
  }

  public trigger (type: string, event: Object = {}): void {
    if (this._events.size > 0 && this._events.has(type)) {
      event = Object.assign(event, { type })

      this._events.get(type)?.forEach(fn => fn.call(this, event))
    }
  }
}
