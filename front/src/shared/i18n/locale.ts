export const LOCALES = ['fr', 'en'] as const

export type Locale = (typeof LOCALES)[number]

export const INTL_LOCALES: Record<Locale, string> = {
  fr: 'fr-FR',
  en: 'en-GB',
}

export const LOCALE_STORAGE_KEY = 'shoppy.locale'

export function isLocale(value: unknown): value is Locale {
  return value === 'fr' || value === 'en'
}

export function applyDocumentLocale(locale: Locale): void {
  try {
    document.documentElement.lang = locale
  } catch {
    /* pas de DOM */
  }
}
