export function cartErrorMessage(error: {
  code: string
  message: string
  violations?: Array<{ message: string }>
}): string {
  if (error.code === 'insufficient_product_stock') {
    return 'Stock insuffisant pour ce produit.'
  }

  if (error.code === 'empty_cart') {
    return 'Le panier est vide.'
  }

  if (error.code === 'cart_item_not_found') {
    return "Cet article n'est plus dans le panier."
  }

  if (error.code === 'unauthenticated') {
    return 'Votre session a expiré. Reconnectez-vous pour continuer.'
  }

  const firstViolation = error.violations?.[0]?.message
  if (firstViolation) {
    return firstViolation
  }

  return error.message
}
