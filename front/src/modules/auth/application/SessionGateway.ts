import type { Session } from '../domain/Session'

export interface SessionGateway {
  set(session: Session): void
  clear(): void
}
