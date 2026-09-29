import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'

describe('account security pages', () => {
  it('requests a password reset without confirming the account exists', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, path: '/forgot-password' })

    await userEvent.type(screen.getByLabelText('Adresse email'), 'ada@shoppy.test')
    await userEvent.click(screen.getByRole('button', { name: 'Envoyer le lien' }))

    await waitFor(() => {
      expect(screen.getByRole('status').textContent).toContain('Si un compte existe')
    })
    expect(authRepository.passwordResets).toEqual(['ada@shoppy.test'])
  })

  it('resets the password from the emailed token', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, path: '/reset-password?token=reset-token' })

    await userEvent.type(screen.getByLabelText('Nouveau mot de passe'), 'brand-new-secret')
    await userEvent.click(screen.getByRole('button', { name: 'Enregistrer' }))

    await waitFor(() => {
      expect(screen.getByRole('status').textContent).toContain('Mot de passe mis à jour')
    })
    expect(authRepository.passwordResetConfirmations).toEqual([
      { token: 'reset-token', password: 'brand-new-secret' },
    ])
  })

  it('confirms the registration email', async () => {
    const authRepository = new FakeAuthRepository()
    const { authSession } = await renderApp({
      authRepository,
      session: { ...visitorSession, user: { ...visitorSession.user, emailVerified: false } },
      path: '/verify-email?token=verify-token',
    })

    await waitFor(() => {
      expect(screen.getByRole('status').textContent).toContain('Votre adresse email est confirmée')
    })
    expect(authRepository.emailVerifications).toEqual(['verify-token'])
    expect(authSession.session.value?.user.emailVerified).toBe(true)
  })

  it('confirms an email change and closes the session', async () => {
    const authRepository = new FakeAuthRepository()
    const { authSession } = await renderApp({
      authRepository,
      session: visitorSession,
      path: '/confirm-email?token=change-token',
    })

    await waitFor(() => {
      expect(screen.getByRole('status').textContent).toContain('Adresse confirmée')
    })
    expect(authRepository.emailChangeConfirmations).toEqual(['change-token'])
    expect(authSession.session.value).toBeNull()
  })
})
