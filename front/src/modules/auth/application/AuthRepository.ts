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
  requestPasswordReset(email: string): Promise<void>
  resetPassword(token: string, password: string): Promise<void>
  verifyEmail(token: string): Promise<void>
  requestEmailVerification(): Promise<void>
  changePassword(currentPassword: string, newPassword: string): Promise<void>
  requestEmailChange(email: string, currentPassword: string): Promise<void>
  confirmEmailChange(token: string): Promise<void>
  logoutAll(): Promise<void>
  deleteAccount(password: string): Promise<void>
}
