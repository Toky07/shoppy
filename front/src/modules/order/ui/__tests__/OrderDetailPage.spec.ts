import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../testing/FakeOrderRepository'
import { cancelledOrder, paidOrder, pendingOrder } from '../../testing/orderFixtures'
import { FakePaymentRepository } from '@/modules/payment/testing/FakePaymentRepository'
import { pendingPayment } from '@/modules/payment/testing/paymentFixtures'

const payButton = () => screen.getByRole('button', { name: /Procéder au paiement/ })

describe('OrderDetailPage', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('asks a guest to log in', async () => {
    await renderApp({ path: `/orders/${pendingOrder.id}` })

    expect(screen.getByText('Vous devez être connecté pour voir les détails de cette commande.')).toBeTruthy()
    expect(
      screen
        .getAllByRole('link', { name: 'Se connecter' })
        .some((link) => link.getAttribute('href') === `/login?redirect=/orders/${pendingOrder.id}`),
    ).toBe(true)
  })

  it('shows a pending order with pay and cancel actions', async () => {
    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository: new FakePaymentRepository(pendingPayment),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: /Commande/ })).toBeTruthy()
      expect(screen.getByText('En attente')).toBeTruthy()
      expect(screen.getByText('Nuvora Tee')).toBeTruthy()
      expect(screen.getByText('Paiement : En attente de paiement')).toBeTruthy()
    })
    expect(payButton()).toBeTruthy()
    expect(screen.getByRole('button', { name: /Annuler la commande/ })).toBeTruthy()
  })

  it('redirects to stripe checkout without marking the order paid', async () => {
    const assign = vi.fn()
    vi.stubGlobal('location', {
      origin: 'http://localhost:5173',
      href: `http://localhost:5173/orders/${pendingOrder.id}`,
      assign,
    })

    const paymentRepository = new FakePaymentRepository(pendingPayment)

    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository,
    })

    await waitFor(() => {
      expect(payButton()).toBeTruthy()
    })

    await userEvent.click(payButton())

    await waitFor(() => {
      expect(paymentRepository.checkouts).toEqual([
        {
          orderId: pendingOrder.id,
          provider: 'stripe',
          successUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=success`,
          cancelUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=cancel`,
        },
      ])
      expect(paymentRepository.completed).toEqual([])
      expect(assign).toHaveBeenCalledWith('https://checkout.test/cs_test_session')
      expect(screen.getByText('En attente')).toBeTruthy()
    })
  })

  it('does not mark the order paid when returning from stripe', async () => {
    const paymentRepository = new FakePaymentRepository(pendingPayment)

    await renderApp({
      path: `/orders/${pendingOrder.id}?payment=success`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository,
    })

    await waitFor(() => {
      expect(screen.getByText('En attente')).toBeTruthy()
      expect(payButton()).toBeTruthy()
    })
    expect(paymentRepository.completed).toEqual([])
    expect(paymentRepository.checkouts).toEqual([])
  })

  it('cancels a pending order', async () => {
    const orderRepository = new FakeOrderRepository([pendingOrder])
    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository,
      paymentRepository: new FakePaymentRepository(pendingPayment),
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: /Annuler la commande/ })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: /Annuler la commande/ }))

    await waitFor(() => {
      expect(orderRepository.cancelled).toEqual([pendingOrder.id])
      expect(screen.getByText('Annulée')).toBeTruthy()
      expect(screen.queryByRole('button', { name: /Procéder au paiement/ })).toBeNull()
    })
  })

  it('shows a payment error', async () => {
    const paymentRepository = new FakePaymentRepository(pendingPayment)
    paymentRepository.checkoutError = new ApiError(409, 'payment_not_payable', 'Not payable.')
    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository,
    })

    await waitFor(() => {
      expect(payButton()).toBeTruthy()
    })

    await userEvent.click(payButton())

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Cette commande ne peut plus être payée.')
    })
  })

  it('hides actions on a paid order', async () => {
    await renderApp({
      path: `/orders/${paidOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([paidOrder]),
      paymentRepository: new FakePaymentRepository({
        ...pendingPayment,
        status: 'completed',
        completedAt: '2026-09-10T12:10:00+00:00',
      }),
    })

    await waitFor(() => {
      expect(screen.getByText('Payée')).toBeTruthy()
    })
    expect(screen.queryByRole('button', { name: /Procéder au paiement/ })).toBeNull()
    expect(screen.queryByRole('button', { name: /Annuler la commande/ })).toBeNull()
  })

  it('shows a dedicated message when the order is missing', async () => {
    await renderApp({
      path: `/orders/${cancelledOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
    })

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Cette commande est introuvable.')
    })
  })
})
