export class InvalidResponseError extends Error {
  constructor(message = 'Invalid API response.') {
    super(message)
    this.name = 'InvalidResponseError'
  }
}
