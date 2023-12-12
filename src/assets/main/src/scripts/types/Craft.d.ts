/**
 * An instance of Craft.
 */
declare const Craft: {
  AssetIndex: any
  cp: Cp
  getActionUrl: (action: string, params?: Object | string) => string
  queue: CraftQueue
  sendActionRequest: (method: string, action: string, options?: object) => Promise<import('../responses').PreviewResponse>
  t: (category: string, message: string, params?: object) => string
}

/**
 * An interface for Craft control panel functionality.
 */
interface Cp {
  displayError: (message: string) => void
}

/**
 * The Craft job queue.
 */
interface CraftQueue {
  push: (job: Function) => void
}
