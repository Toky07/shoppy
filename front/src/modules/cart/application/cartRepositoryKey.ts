import type { InjectionKey } from 'vue'
import type { CartRepository } from './CartRepository'

export const cartRepositoryKey: InjectionKey<CartRepository> = Symbol('cartRepository')
