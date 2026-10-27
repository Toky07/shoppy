import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../../testing/FakeOrderRepository'
import { cancelledOrder, paidOrder, pendingOrder } from '../../../testing/orderFixtures'
import { FakePaymentRepository } from '@/modules/payment/testing/FakePaymentRepository'
import { pendingPayment } from '@/modules/payment/testing/paymentFixtures'

const payButton = () => screen.getByRole('button', { name: /Procéder au paiement/ })

describe('OrderDetailPage', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('asks a guest to log in', async () => {
    const { router } = await renderApp({ path: `/orders/${pendingOrder.id}` })

    expect(router.currentRoute.value.path).toBe('/login')
    expect(router.currentRoute.value.query.redirect).toBe(`/orders/${pendingOrder.id}`)
    expect(screen.getByRole('heading', { name: 'Connexion' })).toBeTruthy()
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
      expect(screen.getAllByText('Ada Lovelace').length).toBe(2)
      expect(screen.getByText(/Livraison Standard/)).toBeTruthy()
      expect(screen.getByText(/4,90/)).toBeTruthy()
      expect(screen.getByText('Paiement : En attente de paiement')).toBeTruthy()
    })
    expect(payButton()).toBeTruthy()
    expect(screen.getByRole('button', { name: /Annuler la commande/ })).toBeTruthy()
  })

  it('completes a local checkout immediately instead of staying on the pending order', async () => {
    const assign = vi.fn()
    vi.stubGlobal('location', {
      origin: 'http://localhost:5173',
      href: `http://localhost:5173/orders/${pendingOrder.id}`,
      assign,
    })

    const orderRepository = new FakeOrderRepository([pendingOrder])
    const paymentRepository = new FakePaymentRepository(pendingPayment)
    paymentRepository.checkoutResult = {
      provider: 'local',
      status: 'pending',
      completedImmediately: true,
      redirectUrl: null,
    }
    paymentRepository.onComplete = (orderId) => {
      orderRepository.replace({ ...pendingOrder, id: orderId, status: 'paid' })
    }

    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository,
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
          successUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=success`,
          cancelUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=cancel`,
        },
      ])
      expect(paymentRepository.completed).toEqual([pendingOrder.id])
      expect(assign).not.toHaveBeenCalled()
      expect(screen.getByText('Payée')).toBeTruthy()
    })
    expect(screen.queryByRole('button', { name: /Procéder au paiement/ })).toBeNull()
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
          successUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=success`,
          cancelUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=cancel`,
        },
      ])
      expect(paymentRepository.completed).toEqual([])
      expect(assign).toHaveBeenCalledWith('https://checkout.test/cs_test_session')
      expect(screen.getByText('En attente')).toBeTruthy()
    })
  })

  it('confirms the stripe payment when returning from checkout', async () => {
    const orderRepository = new FakeOrderRepository([pendingOrder])
    const paymentRepository = new FakePaymentRepository(pendingPayment)
    paymentRepository.onComplete = (orderId) => {
      orderRepository.replace({ ...pendingOrder, id: orderId, status: 'paid' })
    }

    await renderApp({
      path: `/orders/${pendingOrder.id}?payment=success`,
      session: visitorSession,
      orderRepository,
      paymentRepository,
    })

    await waitFor(() => {
      expect(paymentRepository.confirmed).toEqual([pendingOrder.id])
      expect(screen.getByText('Payée')).toBeTruthy()
    })
    expect(paymentRepository.checkouts).toEqual([])
    expect(screen.queryByRole('button', { name: /Procéder au paiement/ })).toBeNull()
  })

  it('shows a notice when stripe checkout is cancelled', async () => {
    await renderApp({
      path: `/orders/${pendingOrder.id}?payment=cancel`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository: new FakePaymentRepository(pendingPayment),
    })

    await waitFor(() => {
      expect(screen.getByText('Le paiement a été annulé. Vous pouvez réessayer.')).toBeTruthy()
      expect(payButton()).toBeTruthy()
    })
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
