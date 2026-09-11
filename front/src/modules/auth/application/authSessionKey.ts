import type { InjectionKey } from 'vue'
import type { AuthSession } from './createAuthSession'

export const authSessionKey: InjectionKey<AuthSession> = Symbol('authSession')
