import { computed, readonly, ref } from 'vue'
import type { Session } from '../domain/Session'
import type { SessionStore } from './SessionStore'

const ephemeralSession: SessionStore = {
  read: () => null,
  write() {},
  clear() {},
}

export function createAuthSession(store: SessionStore = ephemeralSession) {
  const session = ref<Session | null>(store.read())

  function set(next: Session) {
    session.value = next
    store.write(next)
  }

  function clear() {
    session.value = null
    store.clear()
  }

  return {
    session: readonly(session),
    isAuthenticated: computed(() => session.value !== null),
    isAdmin: computed(() => session.value?.user.role === 'admin'),
    set,
    clear,
  }
}

export type AuthSession = ReturnType<typeof createAuthSession>
