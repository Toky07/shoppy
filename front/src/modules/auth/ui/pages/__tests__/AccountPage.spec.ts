import { screen, waitFor, within } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { adminSession, visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'

function page() {
  return within(screen.getByRole('main'))
}

describe('AccountPage', () => {
  it('asks a guest to log in', async () => {
    await renderApp({ path: '/account' })

    expect(page().getByRole('link', { name: 'Se connecter' }).getAttribute('href')).toBe(
      '/login?redirect=/account',
    )
  })

  it('shows the account identity', async () => {
    await renderApp({ session: visitorSession, path: '/account' })

    expect(page().getByRole('heading', { name: 'Mon profil', level: 1 })).toBeTruthy()
    expect(page().getByText('visitor@shoppy.test')).toBeTruthy()
    expect(page().getByText('Client')).toBeTruthy()
    expect(page().getByText(visitorSession.user.id)).toBeTruthy()
  })

  it('names the admin role', async () => {
    await renderApp({ session: adminSession, path: '/account' })

    expect(page().getByText('Administrateur')).toBeTruthy()
  })

  it('links to the customer pages', async () => {
    await renderApp({ session: visitorSession, path: '/account' })

    expect(page().getByRole('link', { name: /Mes commandes/ }).getAttribute('href')).toBe('/orders')
    expect(page().getByRole('link', { name: /Ma liste d'envies/ }).getAttribute('href')).toBe(
      '/favorites',
    )
    expect(page().getByRole('link', { name: /Mon panier/ }).getAttribute('href')).toBe('/cart')
  })

  it('logs out from the profile', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, session: visitorSession, path: '/account' })

    await userEvent.click(page().getByRole('button', { name: 'Se déconnecter' }))

    await waitFor(() => {
      expect(authRepository.logoutCount).toBe(1)
    })
  })

  it('changes the password and leaves the account', async () => {
    const authRepository = new FakeAuthRepository()
    const { router } = await renderApp({ authRepository, session: visitorSession, path: '/account' })

    await userEvent.type(page().getByLabelText('Mot de passe actuel'), 'secret-secret')
    await userEvent.type(page().getByLabelText('Nouveau mot de passe'), 'brand-new-secret')
    await userEvent.click(page().getByRole('button', { name: 'Mettre à jour le mot de passe' }))

    await waitFor(() => {
      expect(router.currentRoute.value.path).toBe('/login')
    })
    expect(authRepository.passwordChanges).toEqual([
      { currentPassword: 'secret-secret', newPassword: 'brand-new-secret' },
    ])
  })

  it('asks to confirm an unverified email', async () => {
    await renderApp({
      session: { ...visitorSession, user: { ...visitorSession.user, emailVerified: false } },
      path: '/account',
    })

    expect(page().getByText('Email à confirmer')).toBeTruthy()
    expect(page().getByRole('button', { name: "Renvoyer l'email" })).toBeTruthy()
  })
})
