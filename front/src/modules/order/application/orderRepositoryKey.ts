import type { InjectionKey } from 'vue'
import type { OrderRepository } from './OrderRepository'

export const orderRepositoryKey: InjectionKey<OrderRepository> = Symbol('orderRepository')
