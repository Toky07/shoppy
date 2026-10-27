import { describe, expect, it } from 'vitest'
import { COOKIE_CONSENT_KEY, createLocalCookieConsentStore } from '../cookieConsentStore'

describe('createLocalCookieConsentStore', () => {
  it('reads and writes a valid choice', () => {
    const storage = new MemoryStorage()
    const store = createLocalCookieConsentStore(storage)

    expect(store.read()).toBeNull()
    store.write('all')
    expect(store.read()).toBe('all')
    expect(storage.getItem(COOKIE_CONSENT_KEY)).toBe('all')
  })

  it('ignores an invalid stored value', () => {
    const storage = new MemoryStorage()
    storage.setItem(COOKIE_CONSENT_KEY, 'maybe')
    const store = createLocalCookieConsentStore(storage)

    expect(store.read()).toBeNull()
  })
})

class MemoryStorage implements Storage {
  private readonly values = new Map<string, string>()

  get length(): number {
    return this.values.size
  }

  clear(): void {
    this.values.clear()
  }

  getItem(key: string): string | null {
    return this.values.get(key) ?? null
  }

  key(index: number): string | null {
    return [...this.values.keys()][index] ?? null
  }

  removeItem(key: string): void {
    this.values.delete(key)
  }

  setItem(key: string, value: string): void {
    this.values.set(key, value)
  }
}
