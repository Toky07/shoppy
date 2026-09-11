import type { Session } from '../domain/Session'
import type { User } from '../domain/User'

export type AuthCredentials = {
  email: string
  password: string
}

export interface AuthRepository {
  login(credentials: AuthCredentials): Promise<Session>
  register(credentials: AuthCredentials): Promise<User>
  logout(): Promise<void>
}
