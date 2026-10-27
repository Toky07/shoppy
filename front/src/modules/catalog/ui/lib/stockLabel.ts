import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'

export function stockLabel(stock: number, t: Translate = translateFr): string {
  return stock > 0 ? t('catalog.inStock', { count: stock }) : t('catalog.outOfStock')
}
