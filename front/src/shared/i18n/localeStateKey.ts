import type { InjectionKey } from 'vue'
import type { LocaleState } from './createLocaleState'

export const localeStateKey: InjectionKey<LocaleState> = Symbol('localeState')
