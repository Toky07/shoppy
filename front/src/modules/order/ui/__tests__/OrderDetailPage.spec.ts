import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeOrderRepository } from '../../testing/FakeOrderRepository'
import { cancelledOrder, paidOrder, pendingOrder } from '../../testing/orderFixtures'
import { FakePaymentRepository } from '@/modules/payment/testing/FakePaymentRepository'
import { pendingPayment } from '@/modules/payment/testing/paymentFixtures'

describe('OrderDetailPage', () => {
  it('asks a guest to log in', async () => {
    await renderApp({ path: `/orders/${pendingOrder.id}` })

    expect(screen.getByText('Connectez-vous pour voir cette commande.')).toBeTruthy()
    expect(
      screen
        .getAllByRole('link', { name: 'Connexion' })
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
      expect(screen.getByRole('heading', { name: 'Commande du 10 septembre 2026' })).toBeTruthy()
      expect(screen.getByText('En attente')).toBeTruthy()
      expect(screen.getByText('Nuvora Tee')).toBeTruthy()
      expect(screen.getByText('Paiement : En attente de paiement')).toBeTruthy()
    })
    expect(screen.getByRole('button', { name: 'Payer' })).toBeTruthy()
    expect(screen.getByRole('button', { name: 'Annuler la commande' })).toBeTruthy()
  })

  it('pays a pending order', async () => {
    const orderRepository = new FakeOrderRepository([pendingOrder])
    const paymentRepository = new FakePaymentRepository(pendingPayment)
    paymentRepository.onComplete = () => {
      orderRepository.replace(paidOrder)
    }

    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository,
      paymentRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Payer' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Payer' }))

    await waitFor(() => {
      expect(paymentRepository.completed).toEqual([pendingOrder.id])
      expect(screen.getByText('Payée')).toBeTruthy()
      expect(screen.getByText('Paiement : Payé')).toBeTruthy()
      expect(screen.queryByRole('button', { name: 'Payer' })).toBeNull()
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
      expect(screen.getByRole('button', { name: 'Annuler la commande' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Annuler la commande' }))

    await waitFor(() => {
      expect(orderRepository.cancelled).toEqual([pendingOrder.id])
      expect(screen.getByText('Annulée')).toBeTruthy()
      expect(screen.queryByRole('button', { name: 'Payer' })).toBeNull()
    })
  })

  it('shows a payment error', async () => {
    const paymentRepository = new FakePaymentRepository(pendingPayment)
    paymentRepository.completeError = new ApiError(409, 'payment_not_payable', 'Not payable.')
    await renderApp({
      path: `/orders/${pendingOrder.id}`,
      session: visitorSession,
      orderRepository: new FakeOrderRepository([pendingOrder]),
      paymentRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Payer' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Payer' }))

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
    expect(screen.queryByRole('button', { name: 'Payer' })).toBeNull()
    expect(screen.queryByRole('button', { name: 'Annuler la commande' })).toBeNull()
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
