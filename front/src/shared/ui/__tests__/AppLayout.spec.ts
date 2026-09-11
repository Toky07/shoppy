import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { adminSession, visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'
import { FakeCartRepository } from '@/modules/cart/testing/FakeCartRepository'
import { filledCart } from '@/modules/cart/testing/cartFixtures'

describe('AppLayout auth nav', () => {
  it('shows login links when logged out', async () => {
    await renderApp()

    expect(screen.getByRole('link', { name: 'Connexion' })).toBeTruthy()
    expect(screen.getByRole('link', { name: 'Inscription' })).toBeTruthy()
  })

  it('logs out from the header', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, session: visitorSession })

    expect(screen.getByText('visitor@shoppy.test')).toBeTruthy()
    await userEvent.click(screen.getByRole('button', { name: 'Déconnexion' }))

    await waitFor(() => {
      expect(screen.getByRole('link', { name: 'Connexion' })).toBeTruthy()
    })
    expect(authRepository.logoutCount).toBe(1)
  })

  it('shows an admin link for an admin', async () => {
    await renderApp({ session: adminSession })

    expect(screen.getByRole('link', { name: 'Admin' }).getAttribute('href')).toBe('/admin')
  })

  it('hides the admin link for a customer', async () => {
    await renderApp({ session: visitorSession })

    expect(screen.queryByRole('link', { name: 'Admin' })).toBeNull()
    expect(screen.getByRole('link', { name: 'Commandes' }).getAttribute('href')).toBe('/orders')
  })

  it('shows a cart badge when the cart has items', async () => {
    await renderApp({
      session: visitorSession,
      cartRepository: new FakeCartRepository(filledCart),
    })

    await waitFor(() => {
      expect(screen.getByRole('link', { name: 'Panier (2)' }).getAttribute('href')).toBe('/cart')
    })
  })

  it('shows a cart link without a badge when logged out', async () => {
    await renderApp()

    expect(screen.getByRole('link', { name: 'Panier' }).getAttribute('href')).toBe('/cart')
  })
})
