import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'

export function newsletterErrorMessage(
  error: {
    code: string
    message: string
    violations?: Array<{ field?: string; message: string }>
  },
  t: Translate = translateFr,
): string {
  if (error.code === 'validation_error' || error.violations?.some((violation) => violation.field === 'email')) {
    return t('newsletter.invalidEmail')
  }

  if (error.code === 'rate_limited') {
    return t('newsletter.rateLimited')
  }

  return error.message
}
