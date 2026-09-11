import type { CatalogRepository, ListProductsQuery } from './CatalogRepository'

export function listProducts(repository: CatalogRepository, query: ListProductsQuery) {
  return repository.list(query)
}
