import type { Session } from '../domain/Session'

export interface SessionStore {
  read(): Session | null
  write(session: Session): void
  clear(): void
}
