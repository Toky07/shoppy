import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { User, UserRole } from '../domain/User'

function isRole(value: unknown): value is UserRole {
  return value === 'customer' || value === 'admin'
}

export function mapUser(payload: unknown): User {
  if (
    !isRecord(payload) ||
    typeof payload.id !== 'string' ||
    typeof payload.email !== 'string' ||
    typeof payload.createdAt !== 'string' ||
    !isRole(payload.role)
  ) {
    throw new InvalidResponseError('Invalid user payload.')
  }

  return {
    id: payload.id,
    email: payload.email,
    role: payload.role,
    createdAt: payload.createdAt,
  }
}
