import type { Money } from './Money'

export function formatMoney(money: Money, locale = 'fr-FR'): string {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: money.currency,
  }).format(money.cents / 100)
}
