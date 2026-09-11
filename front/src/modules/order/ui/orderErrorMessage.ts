export function orderErrorMessage(error: {
  code: string
  message: string
  violations?: Array<{ message: string }>
}): string {
  if (error.code === 'order_not_found' || error.code === 'forbidden') {
    return 'Cette commande est introuvable.'
  }

  if (error.code === 'invalid_order_transition') {
    return 'Cette commande ne peut plus être annulée.'
  }

  if (error.code === 'payment_not_payable' || error.code === 'payment_already_completed') {
    return 'Cette commande ne peut plus être payée.'
  }

  if (error.code === 'payment_charge_failed') {
    return 'Le paiement a échoué.'
  }

  const firstViolation = error.violations?.[0]?.message
  if (firstViolation) {
    return firstViolation
  }

  return error.message
}
