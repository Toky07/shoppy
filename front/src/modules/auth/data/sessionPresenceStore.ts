import type { Session } from '../domain/Session'
import type { SessionStore } from '../application/SessionStore'

export const SESSION_PRESENCE_KEY = 'shoppy.has-session'

export type SessionPresenceStore = SessionStore & {
  remembered(): boolean
}

export function createMemorySessionPresenceStore(initial = false): SessionPresenceStore {
  let remembered = initial

  return {
    read: () => null,
    write(_session: Session) {
      remembered = true
    },
    clear() {
      remembered = false
    },
    remembered: () => remembered,
  }
}

export function createLocalSessionPresenceStore(storage: Storage): SessionPresenceStore {
  return {
    read: () => null,
    write() {
      try {
        storage.setItem(SESSION_PRESENCE_KEY, '1')
      } catch {
        /* stockage indisponible */
      }
    },
    clear() {
      try {
        storage.removeItem(SESSION_PRESENCE_KEY)
      } catch {
        /* stockage indisponible */
      }
    },
    remembered() {
      try {
        return storage.getItem(SESSION_PRESENCE_KEY) === '1'
      } catch {
        return false
      }
    },
  }
}
