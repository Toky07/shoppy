import type { IconName } from '@/shared/ui/icons'

export type AccountShortcut = {
  to: string
  label: string
  icon: IconName
  detail: string
}

function countLabel(count: number, singular: string, plural: string, empty: string) {
  if (count === 0) {
    return empty
  }

  return `${count} ${count > 1 ? plural : singular}`
}

export function accountShortcuts(favoriteCount: number, cartCount: number): AccountShortcut[] {
  return [
    {
      to: '/orders',
      label: 'Mes commandes',
      icon: 'package',
      detail: 'Suivi, statuts et justificatifs',
    },
    {
      to: '/favorites',
      label: "Ma liste d'envies",
      icon: 'heart',
      detail: countLabel(
        favoriteCount,
        'produit gardé',
        'produits gardés',
        'Aucun produit pour le moment',
      ),
    },
    {
      to: '/cart',
      label: 'Mon panier',
      icon: 'cart',
      detail: countLabel(cartCount, 'article en attente', 'articles en attente', 'Votre panier est vide'),
    },
  ]
}
