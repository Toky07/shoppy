import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../testing/FakeOrderRepository'
import { cancelledOrder, pendingOrder } from '../../testing/orderFixtures'

describe('OrderListPage', () => {
  it('asks a guest to log in', async () => {
    await renderApp({ path: '/orders' })

    expect(screen.getByRole('heading', { name: 'Commandes' })).toBeTruthy()
    expect(screen.getByText('Connectez-vous pour voir vos commandes.')).toBeTruthy()
    expect(
      screen
        .getAllByRole('link', { name: 'Connexion' })
        .some((link) => link.getAttribute('href') === '/login?redirect=/orders'),
    ).toBe(true)
  })

  it('shows an empty state', async () => {
    await renderApp({
      path: '/orders',
      session: visitorSession,
      orderRepository: new FakeOrderRepository([]),
    })

    await waitFor(() => {
      expect(screen.getByText('Aucune commande pour le moment.')).toBeTruthy()
    })
  })

  it('lists orders with a link to the detail', async () => {
    await renderApp({
      path: '/orders',
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder, cancelledOrder]),
    })

    await waitFor(() => {
      expect(screen.getByText(/En attente/)).toBeTruthy()
      expect(screen.getByText(/Annulée/)).toBeTruthy()
    })

    expect(
      screen.getByRole('link', { name: 'Voir la commande du 10 septembre 2026' }).getAttribute('href'),
    ).toBe(`/orders/${pendingOrder.id}`)
  })
})
