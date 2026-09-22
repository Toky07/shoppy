import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeCartRepository } from '../../testing/FakeCartRepository'
import { emptyCart, filledCart, parisCheckout, pendingCheckout } from '../../testing/cartFixtures'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'

describe('CartPage', () => {
  async function fillDeliveryAddress() {
    await userEvent.type(screen.getByLabelText('Destinataire'), 'Ada Lovelace')
    await userEvent.type(screen.getByLabelText('Adresse'), '10 rue de la Paix')
    await userEvent.type(screen.getByLabelText('Code postal'), '75002')
    await userEvent.type(screen.getByLabelText('Ville'), 'Paris')
  }
  it('shows an empty cart to a guest', async () => {
    await renderApp({ path: '/cart' })

    expect(screen.getByRole('heading', { name: 'Votre Panier' })).toBeTruthy()
    expect(screen.getByRole('heading', { name: 'Votre panier est vide' })).toBeTruthy()
  })

  it('shows an empty cart', async () => {
    await renderApp({
      path: '/cart',
      session: visitorSession,
      cartRepository: new FakeCartRepository(emptyCart()),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Votre panier est vide' })).toBeTruthy()
    })
  })

  it('shows cart lines, updates quantity, removes an item and checks out', async () => {
    const cartRepository = new FakeCartRepository(filledCart)
    await renderApp({
      path: '/cart',
      session: visitorSession,
      cartRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(screen.getByLabelText('Quantité Nuvora Tee')).toBeTruthy()
      expect(screen.getAllByText(/39,98/).length).toBeGreaterThan(0)
    })

    const quantity = screen.getByLabelText('Quantité Nuvora Tee')
    await userEvent.clear(quantity)
    await userEvent.type(quantity, '3')
    await userEvent.tab()

    await waitFor(() => {
      expect(cartRepository.updated).toEqual([{ productId: nuvoraTee.id, quantity: 3 }])
    })

    await userEvent.click(screen.getByRole('button', { name: /Retirer/ }))

    await waitFor(() => {
      expect(cartRepository.removed).toEqual([nuvoraTee.id])
      expect(screen.getByRole('heading', { name: 'Votre panier est vide' })).toBeTruthy()
    })
  })

  it('clears the cart', async () => {
    const cartRepository = new FakeCartRepository(filledCart)
    await renderApp({
      path: '/cart',
      session: visitorSession,
      cartRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: /Vider le panier/ })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: /Vider le panier/ }))

    await waitFor(() => {
      expect(cartRepository.clearCount).toBe(1)
      expect(screen.getByRole('heading', { name: 'Votre panier est vide' })).toBeTruthy()
    })
  })

  it('creates an order from checkout', async () => {
    const cartRepository = new FakeCartRepository(filledCart)
    await renderApp({
      path: '/cart',
      session: visitorSession,
      cartRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: /Valider ma commande/ })).toBeTruthy()
    })

    await fillDeliveryAddress()
    await userEvent.click(screen.getByRole('button', { name: /Valider ma commande/ }))

    await waitFor(() => {
      expect(cartRepository.checkoutCount).toBe(1)
      expect(cartRepository.checkouts).toEqual([parisCheckout])
      expect(screen.getByRole('status').textContent).toContain(
        `La commande n°${pendingCheckout.id} est enregistrée. Le paiement se fait sur sa page.`,
      )
      expect(screen.getByRole('link', { name: /Payer la commande/ }).getAttribute('href')).toBe(
        `/orders/${pendingCheckout.id}`,
      )
      expect(screen.getByRole('heading', { name: 'Votre panier est vide' })).toBeTruthy()
    })
  })

  it('shows a stock error on checkout', async () => {
    const cartRepository = new FakeCartRepository(filledCart)
    cartRepository.checkoutError = new ApiError(409, 'insufficient_product_stock', 'Not enough stock.')
    await renderApp({
      path: '/cart',
      session: visitorSession,
      cartRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: /Valider ma commande/ })).toBeTruthy()
    })

    await fillDeliveryAddress()
    await userEvent.click(screen.getByRole('button', { name: /Valider ma commande/ }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Stock insuffisant pour ce produit.')
    })
  })
})
