import type { HttpClient } from '@/shared/http/HttpClient'
import type { CatalogRepository, ListProductsQuery } from '../application/CatalogRepository'
import type { Category } from '../domain/Category'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import type { ProductReviewList, SubmitReviewInput } from '../domain/ProductReview'
import { mapCategoryList, mapProduct, mapProductPage, mapProductReviewList } from './productMapper'

export class ProductHttpRepository implements CatalogRepository {
  constructor(private readonly http: HttpClient) {}

  async list(query: ListProductsQuery): Promise<ProductPage> {
    return mapProductPage(
      await this.http.get('/products', {
        page: query.page,
        limit: query.limit,
        ...(query.search !== undefined ? { q: query.search } : {}),
        ...(query.sort !== undefined ? { sort: query.sort } : {}),
        ...(query.minPriceCents !== undefined ? { minPrice: query.minPriceCents } : {}),
        ...(query.maxPriceCents !== undefined ? { maxPrice: query.maxPriceCents } : {}),
        ...(query.inStockOnly ? { inStock: 1 } : {}),
        ...(query.categorySlug !== undefined ? { category: query.categorySlug } : {}),
        ...(query.includeDrafts ? { includeDrafts: 1 } : {}),
      }),
    )
  }

  async listByIds(ids: string[]): Promise<Product[]> {
    if (ids.length === 0) {
      return []
    }

    const page = mapProductPage(await this.http.get('/products', { ids: ids.join(',') }))

    return page.items
  }

  async listCategories(): Promise<Category[]> {
    return mapCategoryList(await this.http.get('/categories'))
  }

  async getById(id: string): Promise<Product> {
    return mapProduct(await this.http.get(`/products/${encodeURIComponent(id)}`))
  }

  async listRelated(id: string): Promise<Product[]> {
    const page = mapProductPage(await this.http.get(`/products/${encodeURIComponent(id)}/related`))

    return page.items
  }

  async listReviews(productId: string): Promise<ProductReviewList> {
    return mapProductReviewList(await this.http.get(`/products/${encodeURIComponent(productId)}/reviews`))
  }

  async submitReview(productId: string, input: SubmitReviewInput): Promise<void> {
    await this.http.post(`/products/${encodeURIComponent(productId)}/reviews`, {
      rating: input.rating,
      body: input.body,
    })
  }
}
