import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'

export function cartErrorMessage(
  error: {
    code: string
    message: string
    violations?: Array<{ message: string }>
  },
  t: Translate = translateFr,
): string {
  if (error.code === 'insufficient_product_stock') {
    return t('errors.insufficientStock')
  }

  if (error.code === 'empty_cart') {
    return t('errors.emptyCart')
  }

  if (error.code === 'cart_already_checking_out') {
    return t('errors.cartCheckingOut')
  }

  if (error.code === 'cart_item_not_found') {
    return t('errors.cartItemMissing')
  }

  if (error.code === 'unauthenticated') {
    return t('errors.sessionExpired')
  }

  if (error.code === 'account_required') {
    return t('errors.accountRequired')
  }

  const firstViolation = error.violations?.[0]?.message
  if (firstViolation) {
    return firstViolation
  }

  return error.message
}
