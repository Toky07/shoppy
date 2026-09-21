import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { User, UserRole } from '../domain/User'
import type { UserPage } from '../domain/UserPage'

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

export function mapUserPage(payload: unknown): UserPage {
  if (
    !isRecord(payload) ||
    !Array.isArray(payload.items) ||
    typeof payload.page !== 'number' ||
    typeof payload.limit !== 'number' ||
    typeof payload.total !== 'number'
  ) {
    throw new InvalidResponseError('Invalid user list payload.')
  }

  return {
    items: payload.items.map(mapUser),
    page: payload.page,
    limit: payload.limit,
    total: payload.total,
  }
}
