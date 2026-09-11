export type ApiViolation = {
  field?: string
  message: string
}

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly code: string,
    message: string,
    public readonly violations: ApiViolation[] = [],
  ) {
    super(message)
    this.name = 'ApiError'
  }
}
