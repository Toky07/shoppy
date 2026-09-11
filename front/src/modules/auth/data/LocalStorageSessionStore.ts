import type { Session } from '../domain/Session'
import type { SessionStore } from '../application/SessionStore'
import { mapSession } from './sessionMapper'

export class LocalStorageSessionStore implements SessionStore {
  constructor(
    private readonly storage: Storage,
    private readonly key = 'shoppy.session',
  ) {}

  read(): Session | null {
    const raw = this.storage.getItem(this.key)
    if (!raw) {
      return null
    }

    try {
      return mapSession(JSON.parse(raw))
    } catch {
      this.storage.removeItem(this.key)
      return null
    }
  }

  write(session: Session): void {
    this.storage.setItem(this.key, JSON.stringify(session))
  }

  clear(): void {
    this.storage.removeItem(this.key)
  }
}
