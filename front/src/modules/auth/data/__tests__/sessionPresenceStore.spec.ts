import { describe, expect, it } from 'vitest'
import { visitorSession } from '../../testing/authFixtures'
import {
  SESSION_PRESENCE_KEY,
  createLocalSessionPresenceStore,
  createMemorySessionPresenceStore,
} from '../sessionPresenceStore'

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

describe('sessionPresenceStore', () => {
  it('remembers a signed-in visit in memory', () => {
    const store = createMemorySessionPresenceStore()

    expect(store.remembered()).toBe(false)
    store.write(visitorSession)
    expect(store.remembered()).toBe(true)
    store.clear()
    expect(store.remembered()).toBe(false)
  })

  it('persists the hint in local storage', () => {
    const storage = createMemoryStorage()
    const store = createLocalSessionPresenceStore(storage)

    store.write(visitorSession)

    expect(storage.getItem(SESSION_PRESENCE_KEY)).toBe('1')
    expect(createLocalSessionPresenceStore(storage).remembered()).toBe(true)

    store.clear()
    expect(storage.getItem(SESSION_PRESENCE_KEY)).toBeNull()
  })
})
