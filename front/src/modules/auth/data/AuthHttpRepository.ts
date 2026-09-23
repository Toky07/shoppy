import type { HttpClient } from '@/shared/http/HttpClient'
import type { AuthCredentials, AuthRepository } from '../application/AuthRepository'
import type { Session } from '../domain/Session'
import type { User } from '../domain/User'
import { mapSession } from './sessionMapper'
import { mapUser } from './userMapper'

export class AuthHttpRepository implements AuthRepository {
  constructor(private readonly http: HttpClient) {}

  async login(credentials: AuthCredentials): Promise<Session> {
    return mapSession(await this.http.post('/auth/login', credentials))
  }

  async register(credentials: AuthCredentials): Promise<void> {
    await this.http.post('/users', credentials)
  }

  async currentUser(): Promise<User> {
    return mapUser(await this.http.get('/auth/me'))
  }

  async logout(): Promise<void> {
    await this.http.post('/auth/logout')
  }

  async requestPasswordReset(email: string): Promise<void> {
    await this.http.post('/auth/password-resets', { email })
  }

  async resetPassword(token: string, password: string): Promise<void> {
    await this.http.post('/auth/password-resets/confirm', { token, password })
  }

  async verifyEmail(token: string): Promise<void> {
    await this.http.post('/auth/email-verifications', { token })
  }

  async requestEmailVerification(): Promise<void> {
    await this.http.post('/auth/email-verifications/request')
  }

  async changePassword(currentPassword: string, newPassword: string): Promise<void> {
    await this.http.post('/auth/password', { currentPassword, newPassword })
  }

  async requestEmailChange(email: string, currentPassword: string): Promise<void> {
    await this.http.post('/auth/email-changes', { email, currentPassword })
  }

  async confirmEmailChange(token: string): Promise<void> {
    await this.http.post('/auth/email-changes/confirm', { token })
  }

  async logoutAll(): Promise<void> {
    await this.http.post('/auth/logout-all')
  }

  async deleteAccount(password: string): Promise<void> {
    await this.http.post('/auth/account/deletion', { password })
  }
}
