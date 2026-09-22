import { ApiError } from '@/shared/http/ApiError'
import type { CatalogRepository, ListProductsQuery } from '../application/CatalogRepository'
import type { Category } from '../domain/Category'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import type { ProductReviewList, SubmitReviewInput } from '../domain/ProductReview'
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

export function createFakeCatalogRepository(
  products: Product[],
  categories: Category[] = [],
  reviews: ProductReviewList = { items: [], count: 0, averageRating: null },
): CatalogRepository {
  return {
    async list(query: ListProductsQuery): Promise<ProductPage> {
      const search = query.search?.trim() ?? ''
      const filtered = products.filter((product) => {
        if (!matchesSearch(product, search)) {
          return false
        }

        if (query.minPriceCents !== undefined && product.price.cents < query.minPriceCents) {
          return false
        }

        if (query.maxPriceCents !== undefined && product.price.cents > query.maxPriceCents) {
          return false
        }

        if (query.categorySlug !== undefined && product.category?.slug !== query.categorySlug) {
          return false
        }

        if (!query.includeDrafts && !product.published) {
          return false
        }

        return !query.inStockOnly || product.stock > 0
      })
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
    async listByIds(ids: string[]): Promise<Product[]> {
      return ids.flatMap((id) => {
        const product = products.find((item) => item.id === id)
        return product?.published ? [product] : []
      })
    },
    async listCategories(): Promise<Category[]> {
      return categories
    },
    async getById(id: string): Promise<Product> {
      const product = products.find((item) => item.id === id || item.slug === id)

      if (!product || (!product.published && id !== product.id)) {
        throw new ApiError(404, 'product_not_found', 'Product not found.')
      }

      return product
    },
    async listRelated(id: string): Promise<Product[]> {
      const current = products.find((item) => item.id === id || item.slug === id)

      if (!current?.category || !current.published) {
        return []
      }

      return products
        .filter(
          (item) =>
            item.published &&
            item.id !== current.id &&
            item.category?.id === current.category?.id,
        )
        .slice(0, 4)
    },
    async listReviews(): Promise<ProductReviewList> {
      return {
        items: reviews.items.map((item) => ({ ...item })),
        count: reviews.count,
        averageRating: reviews.averageRating,
      }
    },
    async submitReview(_productId: string, input: SubmitReviewInput): Promise<void> {
      reviews.items = [
        {
          id: 'review-1',
          rating: input.rating,
          body: input.body,
          author: 'ada',
          createdAt: '2026-09-22T12:00:00+00:00',
          mine: true,
        },
        ...reviews.items.filter((item) => !item.mine),
      ]
      reviews.count = reviews.items.length
      reviews.averageRating =
        reviews.items.reduce((total, item) => total + item.rating, 0) / reviews.items.length
    },
  }
}

export function createFailingCatalogRepository(error: ApiError): CatalogRepository {
  return {
    async list(): Promise<ProductPage> {
      throw error
    },
    async listByIds(): Promise<Product[]> {
      throw error
    },
    async listCategories(): Promise<Category[]> {
      throw error
    },
    async getById(): Promise<Product> {
      throw error
    },
    async listRelated(): Promise<Product[]> {
      throw error
    },
    async listReviews(): Promise<ProductReviewList> {
      throw error
    },
    async submitReview(): Promise<void> {
      throw error
    },
  }
}
