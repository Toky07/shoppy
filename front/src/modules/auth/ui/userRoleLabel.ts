import type { UserRole } from '../domain/User'

const labels: Record<UserRole, string> = {
  customer: 'Client',
  admin: 'Administrateur',
}

export function userRoleLabel(role: UserRole) {
  return labels[role]
}
