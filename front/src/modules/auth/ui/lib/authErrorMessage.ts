import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'

export function authErrorMessage(
  error: {
    code: string
    message: string
    violations?: Array<{ field?: string; message: string }>
  },
  t: Translate = translateFr,
): string {
  if (error.code === 'invalid_credentials') {
    return t('auth.invalidCredentials')
  }

  if (error.code === 'email_already_registered') {
    return t('auth.emailTaken')
  }

  if (error.code === 'last_admin_account') {
    return t('auth.lastAdmin')
  }

  const firstViolation = error.violations?.[0]
  if (firstViolation?.field === 'token') {
    return t('auth.invalidLink')
  }

  if (firstViolation?.message === 'Email is unchanged.') {
    return t('auth.emailUnchanged')
  }

  if (firstViolation?.message) {
    return firstViolation.message
  }

  return error.message
}
