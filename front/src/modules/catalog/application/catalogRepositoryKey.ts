import type { InjectionKey } from 'vue'
import type { CatalogRepository } from './CatalogRepository'

export const catalogRepositoryKey: InjectionKey<CatalogRepository> = Symbol('catalogRepository')
