import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'

describe('RegisterPage', () => {
  it('registers without logging in', async () => {
    const authRepository = new FakeAuthRepository()
    const { router } = await renderApp({ authRepository, path: '/register' })

    await userEvent.type(screen.getByLabelText('Adresse email'), 'Ada@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password1234')
    await userEvent.click(screen.getByRole('button', { name: 'Créer un compte' }))

    await waitFor(() => {
      expect(screen.getByText(/un email vient d'être envoyé/i)).toBeTruthy()
    })
    expect(router.currentRoute.value.path).toBe('/register')
    expect(authRepository.registrations).toHaveLength(1)
    expect(authRepository.logins).toHaveLength(0)
  })
})
