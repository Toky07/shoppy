export type ProductDraft = {
  name: string
  priceEuros: number
  description: string
  stock: number
  categoryId: string
  sku: string
  variants: VariantDraft[]
}

export type VariantDraft = {
  id: string
  sku: string
  size: string
  color: string
  stock: number
}

export function emptyProductDraft(): ProductDraft {
  return {
    name: '',
    priceEuros: 0,
    description: '',
    stock: 0,
    categoryId: '',
    sku: '',
    variants: [],
  }
}
