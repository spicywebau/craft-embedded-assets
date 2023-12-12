export function monkeypatch (Class: any, method: string, callback: Function): void {
  const methodFn = Class.prototype[method]

  Class.prototype[method] = function () {
    methodFn.apply(this, arguments)
    callback.call(this)
  }
}

let uidCounter = 0
export function uniqueId (prefix: string = 'uid'): string {
  return `${prefix}${Math.random().toString(36).substr(2)}${uidCounter++}`
}

export function isUrl (url: string): boolean {
  return /^https?:\/\//.test(url)
}
