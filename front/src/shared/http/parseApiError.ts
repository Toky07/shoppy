import { isRecord } from '@/shared/types/isRecord'
import { ApiError, type ApiViolation } from './ApiError'

function mapViolations(value: unknown): ApiViolation[] {
  if (!Array.isArray(value)) {
    return []
  }

  return value.flatMap((item) => {
    if (!isRecord(item) || typeof item.message !== 'string') {
      return []
    }

    return [
      {
        message: item.message,
        ...(typeof item.field === 'string' ? { field: item.field } : {}),
      },
    ]
  })
}

export function parseApiError(status: number, body: unknown): ApiError {
  if (
    isRecord(body) &&
    isRecord(body.error) &&
    typeof body.error.code === 'string' &&
    typeof body.error.message === 'string'
  ) {
    return new ApiError(status, body.error.code, body.error.message, mapViolations(body.error.violations))
  }

  return new ApiError(status, 'http_error', `HTTP ${status}`)
}
