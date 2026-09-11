import type { InjectionKey } from 'vue'
import type { AdminCatalogRepository } from './AdminCatalogRepository'

export const adminCatalogRepositoryKey: InjectionKey<AdminCatalogRepository> = Symbol('adminCatalogRepository')
