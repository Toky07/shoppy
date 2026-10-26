import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../../testing/FakeOrderRepository'
import { cancelledOrder, pendingOrder } from '../../../testing/orderFixtures'

describe('OrderListPage', () => {
  it('asks a guest to log in', async () => {
    const { router } = await renderApp({ path: '/orders' })

    expect(router.currentRoute.value.path).toBe('/login')
    expect(router.currentRoute.value.query.redirect).toBe('/orders')
    expect(screen.getByRole('heading', { name: 'Connexion' })).toBeTruthy()
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
