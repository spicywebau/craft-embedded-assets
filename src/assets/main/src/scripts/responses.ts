/**
 * An embedded asset preview response.
 */
interface PreviewResponse {
  data?: {
    success: boolean
    payload: any
    error?: string
  }
}

export { PreviewResponse }
