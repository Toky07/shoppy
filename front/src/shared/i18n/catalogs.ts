import type { Locale } from './locale'
import enJson from './locales/en.json'
import fr from './locales/fr.json'
import { interpolate, lookupMessage, type MessageTree, type Translate } from './translate'

export type Messages = typeof fr

const en: Messages = enJson

export const catalogs: Record<Locale, Messages> = { fr, en }

export const translateFr: Translate = (key, params) => translateMessage('fr', key, params)

export function translateMessage(
  locale: Locale,
  key: string,
  params?: Record<string, string | number>,
): string {
  const tree = catalogs[locale] as MessageTree
  const fromLocale = lookupMessage(tree, key)
  const template =
    fromLocale ?? (locale === 'fr' ? undefined : lookupMessage(fr as MessageTree, key)) ?? key

  return interpolate(template, params)
}
