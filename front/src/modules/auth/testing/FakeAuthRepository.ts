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
  public passwordResets: string[] = []
  public passwordResetConfirmations: Array<{ token: string; password: string }> = []
  public emailVerifications: string[] = []
  public emailVerificationRequests = 0
  public passwordChanges: Array<{ currentPassword: string; newPassword: string }> = []
  public emailChanges: Array<{ email: string; currentPassword: string }> = []
  public emailChangeConfirmations: string[] = []
  public logoutAllCount = 0
  public deletions: string[] = []
  public actionError: Error | null = null

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

  async requestPasswordReset(email: string): Promise<void> {
    this.passwordResets.push(email)
    this.raise()
  }

  async resetPassword(token: string, password: string): Promise<void> {
    this.passwordResetConfirmations.push({ token, password })
    this.raise()
  }

  async verifyEmail(token: string): Promise<void> {
    this.emailVerifications.push(token)
    this.raise()
  }

  async requestEmailVerification(): Promise<void> {
    this.emailVerificationRequests += 1
    this.raise()
  }

  async changePassword(currentPassword: string, newPassword: string): Promise<void> {
    this.passwordChanges.push({ currentPassword, newPassword })
    this.raise()
  }

  async requestEmailChange(email: string, currentPassword: string): Promise<void> {
    this.emailChanges.push({ email, currentPassword })
    this.raise()
  }

  async confirmEmailChange(token: string): Promise<void> {
    this.emailChangeConfirmations.push(token)
    this.raise()
  }

  async logoutAll(): Promise<void> {
    this.logoutAllCount += 1
    this.raise()
  }

  async deleteAccount(password: string): Promise<void> {
    this.deletions.push(password)
    this.raise()
  }

  private raise(): void {
    if (this.actionError) {
      throw this.actionError
    }
  }
}
