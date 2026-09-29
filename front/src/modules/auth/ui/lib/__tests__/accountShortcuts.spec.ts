import { describe, expect, it } from 'vitest'
import { accountShortcuts } from '../accountShortcuts'

describe('accountShortcuts', () => {
  it('describes empty favorites and cart', () => {
    const shortcuts = accountShortcuts(0, 0)

    expect(shortcuts).toEqual([
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
        detail: 'Aucun produit pour le moment',
      },
      {
        to: '/cart',
        label: 'Mon panier',
        icon: 'cart',
        detail: 'Votre panier est vide',
      },
    ])
  })

  it('pluralizes counts', () => {
    const shortcuts = accountShortcuts(2, 3)

    expect(shortcuts[1]?.detail).toBe('2 produits gardés')
    expect(shortcuts[2]?.detail).toBe('3 articles en attente')
  })
})
