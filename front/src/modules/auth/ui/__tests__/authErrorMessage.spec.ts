import { describe, expect, it } from 'vitest'
import { authErrorMessage } from '../authErrorMessage'

describe('authErrorMessage', () => {
  it('maps known auth errors', () => {
    expect(authErrorMessage({ code: 'invalid_credentials', message: 'x' })).toBe('Identifiants invalides.')
    expect(authErrorMessage({ code: 'email_already_registered', message: 'x' })).toBe(
      'Cet email est déjà utilisé.',
    )
  })

  it('prefers the first validation violation', () => {
    expect(
      authErrorMessage({
        code: 'validation_error',
        message: 'The request is invalid.',
        violations: [{ message: 'Product name cannot be empty.' }],
      }),
    ).toBe('Product name cannot be empty.')
  })
})
