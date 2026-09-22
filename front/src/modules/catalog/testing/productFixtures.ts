import type { Product } from '../domain/Product'

export const nuvoraTee: Product = {
  id: '550e8400-e29b-41d4-a716-446655440000',
  slug: 'nuvora-tee',
  name: 'Nuvora Tee',
  description: 'Soft cotton tee',
  price: { cents: 1999, currency: 'EUR' },
  stock: 10,
  imageUrl: '/media/products/nuvora-tee.svg',
  imageUrls: ['/media/products/nuvora-tee.svg'],
  createdAt: '2026-08-20T12:00:00+00:00',
  category: null,
  sku: 'NUVORA-TEE',
  variants: [],
  published: true,
}

export const nuvoraTeeGallery: Product = {
  ...nuvoraTee,
  imageUrls: [
    '/media/products/nuvora-tee.svg',
    '/uploads/2026/09/tee-back.jpg',
    '/uploads/2026/09/tee-detail.jpg',
  ],
}

export const outOfStockMug: Product = {
  id: '660e8400-e29b-41d4-a716-446655440001',
  slug: 'nuvora-mug',
  name: 'Nuvora Mug',
  description: null,
  price: { cents: 1299, currency: 'EUR' },
  stock: 0,
  imageUrl: null,
  imageUrls: [],
  createdAt: '2026-08-20T12:00:00+00:00',
  category: null,
  sku: 'NUVORA-MUG',
  variants: [],
  published: true,
}

export function createProduct(overrides: Partial<Product> = {}): Product {
  return { ...nuvoraTee, ...overrides }
}

export function createProductJson(overrides: Record<string, unknown> = {}) {
  return {
    id: nuvoraTee.id,
    slug: nuvoraTee.slug,
    name: nuvoraTee.name,
    description: nuvoraTee.description,
    price: { cents: nuvoraTee.price.cents, currency: nuvoraTee.price.currency },
    stock: nuvoraTee.stock,
    imageUrl: nuvoraTee.imageUrl,
    imageUrls: nuvoraTee.imageUrls,
    createdAt: nuvoraTee.createdAt,
    category: null,
    sku: nuvoraTee.sku,
    variants: [],
    published: true,
    ...overrides,
  }
}
