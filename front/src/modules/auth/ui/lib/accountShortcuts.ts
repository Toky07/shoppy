import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { IconName } from '@/shared/ui/icons'

export type AccountShortcut = {
  to: string
  label: string
  icon: IconName
  detail: string
}

function countLabel(count: number, emptyKey: string, oneKey: string, manyKey: string, t: Translate) {
  if (count === 0) {
    return t(emptyKey)
  }

  return t(count > 1 ? manyKey : oneKey, { count })
}

export function accountShortcuts(
  favoriteCount: number,
  cartCount: number,
  t: Translate = translateFr,
): AccountShortcut[] {
  return [
    {
      to: '/orders',
      label: t('account.orders'),
      icon: 'package',
      detail: t('account.ordersDetail'),
    },
    {
      to: '/favorites',
      label: t('favorites.title'),
      icon: 'heart',
      detail: countLabel(
        favoriteCount,
        'account.favoritesEmpty',
        'account.favoritesOne',
        'account.favoritesMany',
        t,
      ),
    },
    {
      to: '/cart',
      label: t('account.cart'),
      icon: 'cart',
      detail: countLabel(cartCount, 'account.cartEmpty', 'account.cartOne', 'account.cartMany', t),
    },
  ]
}
