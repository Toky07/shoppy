import type { CatalogRepository } from './CatalogRepository'

export function getProduct(repository: CatalogRepository, id: string) {
  return repository.getById(id)
}
