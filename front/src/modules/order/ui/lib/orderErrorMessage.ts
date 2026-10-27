import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'

export function orderErrorMessage(
  error: {
    code: string
    message: string
    violations?: Array<{ message: string }>
  },
  t: Translate = translateFr,
): string {
  if (error.code === 'order_not_found' || error.code === 'forbidden') {
    return t('errors.orderNotFound')
  }

  if (error.code === 'invalid_order_transition') {
    return t('errors.orderNotCancellable')
  }

  if (error.code === 'payment_not_payable' || error.code === 'payment_already_completed') {
    return t('errors.orderNotPayable')
  }

  if (error.code === 'payment_charge_failed' || error.code === 'payment_provider_not_configured') {
    return t('errors.paymentFailed')
  }

  const firstViolation = error.violations?.[0]?.message
  if (firstViolation) {
    return firstViolation
  }

  return error.message
}
