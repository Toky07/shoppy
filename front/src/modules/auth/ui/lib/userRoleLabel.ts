import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { UserRole } from '../../domain/User'

const keys: Record<UserRole, 'auth.roleCustomer' | 'auth.roleAdmin'> = {
  customer: 'auth.roleCustomer',
  admin: 'auth.roleAdmin',
}

export function userRoleLabel(role: UserRole, t: Translate = translateFr) {
  return t(keys[role])
}
