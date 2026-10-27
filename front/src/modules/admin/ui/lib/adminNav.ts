import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { IconName } from '@/shared/ui/icons'

export type AdminNavItem = {
  to: string
  labelKey: string
  icon: IconName
  exact?: boolean
}

export const ADMIN_NAV: AdminNavItem[] = [
  { to: '/admin', labelKey: 'admin.nav.dashboard', icon: 'grid', exact: true },
  { to: '/admin/orders', labelKey: 'admin.nav.orders', icon: 'package' },
  { to: '/admin/products', labelKey: 'admin.nav.catalog', icon: 'tag' },
  { to: '/admin/reviews', labelKey: 'admin.nav.reviews', icon: 'star' },
  { to: '/admin/users', labelKey: 'admin.nav.users', icon: 'users' },
  { to: '/admin/audit', labelKey: 'admin.nav.audit', icon: 'list' },
]

export function isAdminNavActive(path: string, item: AdminNavItem): boolean {
  if (item.exact) {
    return path === item.to
  }

  return path === item.to || path.startsWith(`${item.to}/`)
}

export function adminPageTitle(path: string, t: Translate = translateFr): string {
  if (path.startsWith('/admin/orders/')) {
    return t('admin.titles.orderDetail')
  }

  if (path === '/admin/orders') {
    return t('admin.titles.orders')
  }

  if (path === '/admin/products/new') {
    return t('admin.titles.productNew')
  }

  if (path.startsWith('/admin/products/') && path !== '/admin/products') {
    return t('admin.titles.productEdit')
  }

  if (path === '/admin/products') {
    return t('admin.titles.catalog')
  }

  if (path === '/admin/reviews') {
    return t('admin.titles.reviews')
  }

  if (path === '/admin/users') {
    return t('admin.titles.users')
  }

  if (path === '/admin/audit') {
    return t('admin.titles.audit')
  }

  return t('admin.titles.dashboard')
}
