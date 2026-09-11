import type { AuthCredentials, AuthRepository } from '../application/AuthRepository'
import type { Session } from '../domain/Session'
import type { User } from '../domain/User'
import { visitorSession, visitorUser } from './authFixtures'

export class FakeAuthRepository implements AuthRepository {
  public logins: AuthCredentials[] = []
  public registrations: AuthCredentials[] = []
  public logoutCount = 0
  public loginResult: Session = visitorSession
  public registerResult: User = visitorUser
  public loginError: Error | null = null
  public registerError: Error | null = null

  async login(credentials: AuthCredentials): Promise<Session> {
    this.logins.push(credentials)
    if (this.loginError) {
      throw this.loginError
    }
    return { ...this.loginResult, user: { ...this.loginResult.user, email: credentials.email.toLowerCase() } }
  }

  async register(credentials: AuthCredentials): Promise<User> {
    this.registrations.push(credentials)
    if (this.registerError) {
      throw this.registerError
    }
    return { ...this.registerResult, email: credentials.email.toLowerCase() }
  }

  async logout(): Promise<void> {
    this.logoutCount += 1
  }
}
