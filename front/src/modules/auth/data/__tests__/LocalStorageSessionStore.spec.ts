import { describe, expect, it } from 'vitest'
import { LocalStorageSessionStore } from '../LocalStorageSessionStore'
import { visitorSession } from '../../testing/authFixtures'

function createMemoryStorage(): Storage {
  const values = new Map<string, string>()

  return {
    get length() {
      return values.size
    },
    clear: () => values.clear(),
    getItem: (key) => values.get(key) ?? null,
    setItem: (key, value) => {
      values.set(key, value)
    },
    removeItem: (key) => {
      values.delete(key)
    },
    key: (index) => [...values.keys()][index] ?? null,
  }
}

describe('LocalStorageSessionStore', () => {
  it('round-trips a session', () => {
    const store = new LocalStorageSessionStore(createMemoryStorage())
    store.write(visitorSession)

    expect(store.read()).toEqual(visitorSession)
  })

  it('returns null for invalid JSON', () => {
    const storage = createMemoryStorage()
    storage.setItem('shoppy.session', '{not json')
    const store = new LocalStorageSessionStore(storage)

    expect(store.read()).toBeNull()
  })
})
