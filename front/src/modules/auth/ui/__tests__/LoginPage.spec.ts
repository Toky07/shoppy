import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'

describe('LoginPage', () => {
  it('logs in and redirects to the catalog', async () => {
    const authRepository = new FakeAuthRepository()
    const { router } = await renderApp({ authRepository, path: '/login' })

    await userEvent.type(screen.getByLabelText('Email'), 'visitor@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password123')
    await userEvent.click(screen.getByRole('button', { name: 'Se connecter' }))

    await waitFor(() => {
      expect(router.currentRoute.value.path).toBe('/')
      expect(screen.getByText('visitor@shoppy.test')).toBeTruthy()
    })
    expect(authRepository.logins).toEqual([
      { email: 'visitor@shoppy.test', password: 'password123' },
    ])
  })

  it('shows an invalid credentials error', async () => {
    const authRepository = new FakeAuthRepository()
    authRepository.loginError = new ApiError(401, 'invalid_credentials', 'Invalid credentials.')
    await renderApp({ authRepository, path: '/login' })

    await userEvent.type(screen.getByLabelText('Email'), 'visitor@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password123')
    await userEvent.click(screen.getByRole('button', { name: 'Se connecter' }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Identifiants invalides.')
    })
  })
})
