import type { Product } from '../domain/Product'

export const nuvoraTee: Product = {
  id: '550e8400-e29b-41d4-a716-446655440000',
  name: 'Nuvora Tee',
  description: 'Soft cotton tee',
  price: { cents: 1999, currency: 'EUR' },
  stock: 10,
  imageUrl: '/media/products/nuvora-tee.svg',
  createdAt: '2026-08-20T12:00:00+00:00',
}

export const outOfStockMug: Product = {
  id: '660e8400-e29b-41d4-a716-446655440001',
  name: 'Nuvora Mug',
  description: null,
  price: { cents: 1299, currency: 'EUR' },
  stock: 0,
  imageUrl: null,
  createdAt: '2026-08-20T12:00:00+00:00',
}

export function createProduct(overrides: Partial<Product> = {}): Product {
  return { ...nuvoraTee, ...overrides }
}

export function createProductJson(overrides: Record<string, unknown> = {}) {
  return {
    id: nuvoraTee.id,
    name: nuvoraTee.name,
    description: nuvoraTee.description,
    price: { cents: nuvoraTee.price.cents, currency: nuvoraTee.price.currency },
    stock: nuvoraTee.stock,
    imageUrl: nuvoraTee.imageUrl,
    createdAt: nuvoraTee.createdAt,
    ...overrides,
  }
}
