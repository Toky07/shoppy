import { describe, expect, it } from 'vitest'
import { createLocaleState } from '../createLocaleState'
import { createMemoryLocaleStore } from '../localeStore'

describe('createLocaleState', () => {
  it('starts in french and switches to english', () => {
    const store = createMemoryLocaleStore('fr')
    const i18n = createLocaleState(store)

    expect(i18n.locale.value).toBe('fr')
    expect(i18n.t('nav.catalog')).toBe('Catalogue')
    expect(i18n.intlLocale.value).toBe('fr-FR')

    i18n.setLocale('en')

    expect(i18n.locale.value).toBe('en')
    expect(i18n.t('nav.catalog')).toBe('Catalog')
    expect(i18n.intlLocale.value).toBe('en-GB')
    expect(store.read()).toBe('en')
  })

  it('restores a stored locale', () => {
    const i18n = createLocaleState(createMemoryLocaleStore('en'))

    expect(i18n.locale.value).toBe('en')
    expect(i18n.t('header.login')).toBe('Sign in')
  })
})
