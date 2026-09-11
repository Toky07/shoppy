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

  async register(credentials: AuthCredentials): Promise<User> {
    return mapUser(await this.http.post('/users', credentials))
  }

  async logout(): Promise<void> {
    await this.http.post('/auth/logout')
  }
}
