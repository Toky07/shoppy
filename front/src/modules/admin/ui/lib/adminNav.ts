import type { IconName } from '@/shared/ui/icons'

export type AdminNavItem = {
  to: string
  label: string
  icon: IconName
  exact?: boolean
}

export const ADMIN_NAV: AdminNavItem[] = [
  { to: '/admin', label: 'Tableau de bord', icon: 'grid', exact: true },
  { to: '/admin/orders', label: 'Commandes', icon: 'package' },
  { to: '/admin/products', label: 'Catalogue', icon: 'tag' },
  { to: '/admin/reviews', label: 'Avis', icon: 'star' },
  { to: '/admin/users', label: 'Utilisateurs', icon: 'users' },
  { to: '/admin/audit', label: 'Journal', icon: 'list' },
]

export function isAdminNavActive(path: string, item: AdminNavItem): boolean {
  if (item.exact) {
    return path === item.to
  }

  return path === item.to || path.startsWith(`${item.to}/`)
}

export function adminPageTitle(path: string): string {
  if (path.startsWith('/admin/orders/')) {
    return 'Détail commande'
  }

  if (path === '/admin/orders') {
    return 'Commandes'
  }

  if (path === '/admin/products/new') {
    return 'Nouveau produit'
  }

  if (path.startsWith('/admin/products/') && path !== '/admin/products') {
    return 'Modifier le produit'
  }

  if (path === '/admin/products') {
    return 'Catalogue'
  }

  if (path === '/admin/reviews') {
    return 'Avis'
  }

  if (path === '/admin/users') {
    return 'Utilisateurs'
  }

  if (path === '/admin/audit') {
    return 'Journal'
  }

  return 'Tableau de bord'
}
