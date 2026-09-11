import { ApiError } from './ApiError'

export function toApiError(caught: unknown, fallbackMessage = 'Une erreur est survenue.'): ApiError {
  return caught instanceof ApiError ? caught : new ApiError(0, 'internal_error', fallbackMessage)
}
