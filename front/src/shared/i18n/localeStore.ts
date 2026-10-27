import { isLocale, LOCALE_STORAGE_KEY, type Locale } from './locale'

export type LocaleStore = {
  read(): Locale | null
  write(locale: Locale): void
}

export function createMemoryLocaleStore(locale: Locale | null = null): LocaleStore {
  let value = locale

  return {
    read: () => value,
    write: (next) => {
      value = next
    },
  }
}

export function createLocalLocaleStore(storage: Storage): LocaleStore {
  return {
    read() {
      try {
        const stored = storage.getItem(LOCALE_STORAGE_KEY)
        return isLocale(stored) ? stored : null
      } catch {
        return null
      }
    },
    write(locale) {
      try {
        storage.setItem(LOCALE_STORAGE_KEY, locale)
      } catch {
        /* stockage indisponible */
      }
    },
  }
}
