import type { InjectionKey } from 'vue'
import type { CartState } from './createCartState'

export const cartStateKey: InjectionKey<CartState> = Symbol('cartState')
