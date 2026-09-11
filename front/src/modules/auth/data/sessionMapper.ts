import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { Session } from '../domain/Session'
import { mapUser } from './userMapper'

export function mapSession(payload: unknown): Session {
  if (!isRecord(payload) || typeof payload.accessToken !== 'string') {
    throw new InvalidResponseError('Invalid session payload.')
  }

  return {
    accessToken: payload.accessToken,
    user: mapUser(payload.user),
  }
}
