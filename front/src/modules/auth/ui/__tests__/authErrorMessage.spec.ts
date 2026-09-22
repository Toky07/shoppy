import { describe, expect, it } from 'vitest'
import { authErrorMessage } from '../authErrorMessage'

describe('authErrorMessage', () => {
  it('maps known auth errors', () => {
    expect(authErrorMessage({ code: 'invalid_credentials', message: 'x' })).toBe('Identifiants invalides.')
    expect(authErrorMessage({ code: 'email_already_registered', message: 'x' })).toBe(
      'Cet email est déjà utilisé.',
    )
    expect(authErrorMessage({ code: 'last_admin_account', message: 'x' })).toBe(
      'Le dernier administrateur ne peut pas supprimer son compte.',
    )
    expect(
      authErrorMessage({
        code: 'validation_error',
        message: 'The request is invalid.',
        violations: [{ field: 'token', message: 'The account token is invalid or expired.' }],
      }),
    ).toBe('Ce lien est invalide ou a expiré.')
    expect(
      authErrorMessage({
        code: 'validation_error',
        message: 'The request is invalid.',
        violations: [{ field: 'email', message: 'Email is unchanged.' }],
      }),
    ).toBe('Cette adresse est déjà la vôtre.')
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
