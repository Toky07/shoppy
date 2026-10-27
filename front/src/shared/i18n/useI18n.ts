import { inject } from 'vue'
import { localeStateKey } from './localeStateKey'

export function useI18n() {
  const localeState = inject(localeStateKey)

  if (!localeState) {
    throw new Error('Locale is not provided.')
  }

  return localeState
}
