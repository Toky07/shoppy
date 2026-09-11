import type { InjectionKey } from 'vue'
import type { AuthRepository } from './AuthRepository'

export const authRepositoryKey: InjectionKey<AuthRepository> = Symbol('authRepository')
