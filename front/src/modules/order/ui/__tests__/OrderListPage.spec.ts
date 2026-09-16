import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../testing/FakeOrderRepository'
import { cancelledOrder, pendingOrder } from '../../testing/orderFixtures'

describe('OrderListPage', () => {
  it('asks a guest to log in', async () => {
    await renderApp({ path: '/orders' })

    expect(screen.getByRole('heading', { name: 'Mes Commandes' })).toBeTruthy()
    expect(screen.getByText("Vous devez être connecté pour voir l'historique de vos commandes.")).toBeTruthy()
    expect(
      screen
        .getAllByRole('link', { name: 'Se connecter' })
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
      expect(screen.getByRole('heading', { name: 'Aucune commande' })).toBeTruthy()
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
      screen.getAllByRole('link', { name: /Voir les détails/ })[0]?.getAttribute('href'),
    ).toBe(`/orders/${pendingOrder.id}`)
  })
})
