import type { InjectionKey } from 'vue'
import type { UserDirectory } from './UserDirectory'

export const userDirectoryKey: InjectionKey<UserDirectory> = Symbol('userDirectory')
