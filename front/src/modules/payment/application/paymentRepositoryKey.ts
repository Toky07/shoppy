import type { InjectionKey } from 'vue'
import type { PaymentRepository } from './PaymentRepository'

export const paymentRepositoryKey: InjectionKey<PaymentRepository> = Symbol('paymentRepository')
