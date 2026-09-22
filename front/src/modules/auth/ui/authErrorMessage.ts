export function authErrorMessage(error: {
  code: string
  message: string
  violations?: Array<{ field?: string; message: string }>
}): string {
  if (error.code === 'invalid_credentials') {
    return 'Identifiants invalides.'
  }

  if (error.code === 'email_already_registered') {
    return 'Cet email est déjà utilisé.'
  }

  if (error.code === 'last_admin_account') {
    return 'Le dernier administrateur ne peut pas supprimer son compte.'
  }

  const firstViolation = error.violations?.[0]
  if (firstViolation?.field === 'token') {
    return 'Ce lien est invalide ou a expiré.'
  }

  if (firstViolation?.message === 'Email is unchanged.') {
    return 'Cette adresse est déjà la vôtre.'
  }

  if (firstViolation?.message) {
    return firstViolation.message
  }

  return error.message
}
