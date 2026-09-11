import type { Session } from '../domain/Session'
import type { SessionStore } from '../application/SessionStore'

export class MemorySessionStore implements SessionStore {
  constructor(private session: Session | null = null) {}

  read(): Session | null {
    return this.session
  }

  write(session: Session): void {
    this.session = session
  }

  clear(): void {
    this.session = null
  }
}
