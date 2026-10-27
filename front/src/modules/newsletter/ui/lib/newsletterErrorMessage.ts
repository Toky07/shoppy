export function newsletterErrorMessage(error: {
  code: string
  message: string
  violations?: Array<{ field?: string; message: string }>
}): string {
  if (error.code === 'validation_error' || error.violations?.some((violation) => violation.field === 'email')) {
    return 'Indiquez une adresse email valide.'
  }

  if (error.code === 'rate_limited') {
    return 'Trop de tentatives. Réessayez dans un instant.'
  }

  return error.message
}
