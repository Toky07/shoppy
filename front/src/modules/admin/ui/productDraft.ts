export type ProductDraft = {
  name: string
  priceEuros: number
  description: string
  stock: number
}

export function emptyProductDraft(): ProductDraft {
  return {
    name: '',
    priceEuros: 0,
    description: '',
    stock: 0,
  }
}
