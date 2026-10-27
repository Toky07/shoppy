import { screen, waitFor, within } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { adminSession, visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'
import { FakeCartRepository } from '@/modules/cart/testing/FakeCartRepository'
import { filledCart } from '@/modules/cart/testing/cartFixtures'
import { createCookieConsentState } from '@/shared/consent/createCookieConsentState'

describe('AppLayout auth nav', () => {
  it('shows login links when logged out', async () => {
    await renderApp()

    expect(screen.getByRole('link', { name: 'Connexion' })).toBeTruthy()
    expect(screen.getByRole('link', { name: 'Inscription' })).toBeTruthy()
  })

  it('logs out from the account menu', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, session: visitorSession })

    expect(screen.getByRole('button', { name: 'Mon compte' })).toBeTruthy()
    expect(screen.queryByText('visitor@shoppy.test')).toBeNull()
    await userEvent.click(screen.getByRole('button', { name: 'Mon compte' }))
    expect(screen.getByText('visitor@shoppy.test')).toBeTruthy()
    await userEvent.click(screen.getByRole('menuitem', { name: 'Déconnexion' }))

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

  it('keeps favorites and theme out of the header when logged in', async () => {
    await renderApp({ session: visitorSession })

    expect(screen.queryByRole('link', { name: /^Favoris/ })).toBeNull()
    expect(screen.queryByRole('group', { name: 'Apparence' })).toBeNull()

    await userEvent.click(screen.getByRole('button', { name: 'Mon compte' }))

    expect(screen.getByRole('menuitem', { name: 'Mes favoris' })).toBeTruthy()
    expect(screen.getByRole('group', { name: 'Apparence' })).toBeTruthy()
  })
})

describe('AppLayout footer', () => {
  it('links to the legal pages', async () => {
    await renderApp()

    expect(screen.getByRole('link', { name: 'CGV' }).getAttribute('href')).toBe('/cgv')
    expect(screen.getByRole('link', { name: 'Confidentialité' }).getAttribute('href')).toBe(
      '/confidentialite',
    )
    expect(screen.getByRole('link', { name: 'Mentions légales' }).getAttribute('href')).toBe(
      '/mentions',
    )
    expect(screen.getByRole('link', { name: /^Retours$/ }).getAttribute('href')).toBe('/retours')
    expect(screen.getByRole('link', { name: 'Livraison & retours' }).getAttribute('href')).toBe(
      '/retours',
    )
    expect(screen.getByRole('button', { name: 'Cookies' })).toBeTruthy()
  })
})

describe('AppLayout cookies', () => {
  it('asks for consent until the visitor chooses', async () => {
    await renderApp({ cookieConsent: createCookieConsentState() })

    const banner = screen.getByRole('dialog', { name: 'Cookies' })
    expect(
      within(banner).getByRole('link', { name: 'Politique de confidentialité' }).getAttribute('href'),
    ).toBe('/confidentialite')

    await userEvent.click(within(banner).getByRole('button', { name: 'Refuser' }))

    expect(screen.queryByRole('dialog', { name: 'Cookies' })).toBeNull()
  })

  it('hides the banner when a choice is already stored', async () => {
    await renderApp()

    expect(screen.queryByRole('dialog', { name: 'Cookies' })).toBeNull()
  })

  it('reopens the banner from the footer', async () => {
    await renderApp()

    await userEvent.click(screen.getByRole('button', { name: 'Cookies' }))

    expect(screen.getByRole('dialog', { name: 'Cookies' })).toBeTruthy()
  })
})


