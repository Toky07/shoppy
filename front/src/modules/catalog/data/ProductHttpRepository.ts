import type { HttpClient } from '@/shared/http/HttpClient'
import type { CatalogRepository, ListProductsQuery } from '../application/CatalogRepository'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import { mapProduct, mapProductPage } from './productMapper'

export class ProductHttpRepository implements CatalogRepository {
  constructor(private readonly http: HttpClient) {}

  async list(query: ListProductsQuery): Promise<ProductPage> {
    return mapProductPage(
      await this.http.get('/products', {
        page: query.page,
        limit: query.limit,
        q: query.search,
        sort: query.sort,
      }),
    )
  }

  async getById(id: string): Promise<Product> {
    return mapProduct(await this.http.get(`/products/${encodeURIComponent(id)}`))
  }
}
