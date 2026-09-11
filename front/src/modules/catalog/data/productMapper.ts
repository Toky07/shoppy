import { mapMoney } from '@/shared/money/mapMoney'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'

export function mapProduct(payload: unknown): Product {
  if (
    !isRecord(payload) ||
    typeof payload.id !== 'string' ||
    typeof payload.name !== 'string' ||
    (payload.description !== null && typeof payload.description !== 'string') ||
    typeof payload.stock !== 'number' ||
    (payload.imageUrl !== null && payload.imageUrl !== undefined && typeof payload.imageUrl !== 'string') ||
    typeof payload.createdAt !== 'string'
  ) {
    throw new InvalidResponseError('Invalid product payload.')
  }

  return {
    id: payload.id,
    name: payload.name,
    description: payload.description,
    price: mapMoney(payload.price, 'Invalid product price.'),
    stock: payload.stock,
    imageUrl: typeof payload.imageUrl === 'string' ? payload.imageUrl : null,
    createdAt: payload.createdAt,
  }
}

export function mapProductPage(payload: unknown): ProductPage {
  if (
    !isRecord(payload) ||
    !Array.isArray(payload.items) ||
    typeof payload.page !== 'number' ||
    typeof payload.limit !== 'number' ||
    typeof payload.total !== 'number'
  ) {
    throw new InvalidResponseError('Invalid product list payload.')
  }

  return {
    items: payload.items.map(mapProduct),
    page: payload.page,
    limit: payload.limit,
    total: payload.total,
  }
}
