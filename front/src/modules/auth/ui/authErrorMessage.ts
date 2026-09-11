export function authErrorMessage(error: { code: string; message: string; violations?: Array<{ message: string }> }): string {
  if (error.code === 'invalid_credentials') {
    return 'Identifiants invalides.'
  }

  if (error.code === 'email_already_registered') {
    return 'Cet email est déjà utilisé.'
  }

  const firstViolation = error.violations?.[0]?.message
  if (firstViolation) {
    return firstViolation
  }

  return error.message
}
