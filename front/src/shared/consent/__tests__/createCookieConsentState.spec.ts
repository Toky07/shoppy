import { describe, expect, it } from 'vitest'
import { createCookieConsentState } from '../createCookieConsentState'
import { createMemoryCookieConsentStore } from '../cookieConsentStore'

describe('createCookieConsentState', () => {
  it('asks for a choice when nothing is stored', () => {
    const consent = createCookieConsentState()

    expect(consent.visible.value).toBe(true)
    expect(consent.allowsAnalytics.value).toBe(false)
    expect(consent.choice.value).toBeNull()
  })

  it('accepts optional cookies', () => {
    const store = createMemoryCookieConsentStore()
    const consent = createCookieConsentState(store)

    consent.acceptAll()

    expect(consent.visible.value).toBe(false)
    expect(consent.allowsAnalytics.value).toBe(true)
    expect(consent.choice.value).toBe('all')
    expect(store.read()).toBe('all')
  })

  it('keeps only necessary cookies when refused', () => {
    const store = createMemoryCookieConsentStore()
    const consent = createCookieConsentState(store)

    consent.refuseOptional()

    expect(consent.visible.value).toBe(false)
    expect(consent.allowsAnalytics.value).toBe(false)
    expect(consent.choice.value).toBe('necessary')
    expect(store.read()).toBe('necessary')
  })

  it('hides the banner when a choice is already stored', () => {
    const consent = createCookieConsentState(createMemoryCookieConsentStore('necessary'))

    expect(consent.visible.value).toBe(false)
    expect(consent.allowsAnalytics.value).toBe(false)
  })

  it('reopens the banner so the choice can change', () => {
    const consent = createCookieConsentState(createMemoryCookieConsentStore('necessary'))

    consent.review()

    expect(consent.visible.value).toBe(true)
    expect(consent.choice.value).toBe('necessary')

    consent.acceptAll()

    expect(consent.visible.value).toBe(false)
    expect(consent.allowsAnalytics.value).toBe(true)
  })
})
