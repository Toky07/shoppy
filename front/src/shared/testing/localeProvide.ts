import { createLocaleState } from '@/shared/i18n/createLocaleState'
import type { Locale } from '@/shared/i18n/locale'
import { localeStateKey } from '@/shared/i18n/localeStateKey'
import { createMemoryLocaleStore } from '@/shared/i18n/localeStore'

export function localeProvide(locale: Locale = 'fr') {
  return {
    [localeStateKey as symbol]: createLocaleState(createMemoryLocaleStore(locale)),
  }
}
