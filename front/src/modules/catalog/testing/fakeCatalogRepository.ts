import { ApiError } from '@/shared/http/ApiError'
import type { CatalogRepository, ListProductsQuery } from '../application/CatalogRepository'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import { DEFAULT_PRODUCT_SORT } from '../application/productSort'

function matchesSearch(product: Product, search: string): boolean {
  if (search === '') {
    return true
  }

  const needle = search.toLowerCase()

  return (
    product.name.toLowerCase().includes(needle) ||
    (product.description?.toLowerCase().includes(needle) ?? false)
  )
}

function compareProducts(left: Product, right: Product, sort: ListProductsQuery['sort']): number {
  switch (sort) {
    case 'oldest':
      return left.createdAt.localeCompare(right.createdAt) || left.name.localeCompare(right.name, 'fr')
    case 'price_asc':
      return left.price.cents - right.price.cents || left.name.localeCompare(right.name, 'fr')
    case 'price_desc':
      return right.price.cents - left.price.cents || left.name.localeCompare(right.name, 'fr')
    case 'name_asc':
      return left.name.localeCompare(right.name, 'fr')
    case 'newest':
    default:
      return right.createdAt.localeCompare(left.createdAt) || left.name.localeCompare(right.name, 'fr')
  }
}

export function createFakeCatalogRepository(products: Product[]): CatalogRepository {
  return {
    async list(query: ListProductsQuery): Promise<ProductPage> {
      const search = query.search?.trim() ?? ''
      const filtered = products.filter((product) => matchesSearch(product, search))
      const sorted = [...filtered].sort((left, right) =>
        compareProducts(left, right, query.sort ?? DEFAULT_PRODUCT_SORT),
      )
      const start = (query.page - 1) * query.limit

      return {
        items: sorted.slice(start, start + query.limit),
        page: query.page,
        limit: query.limit,
        total: sorted.length,
      }
    },
    async getById(id: string): Promise<Product> {
      const product = products.find((item) => item.id === id || item.slug === id)

      if (!product) {
        throw new ApiError(404, 'product_not_found', 'Product not found.')
      }

      return product
    },
  }
}

export function createFailingCatalogRepository(error: ApiError): CatalogRepository {
  return {
    async list(): Promise<ProductPage> {
      throw error
    },
    async getById(): Promise<Product> {
      throw error
    },
  }
}
