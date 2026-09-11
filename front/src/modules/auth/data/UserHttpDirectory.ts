import type { HttpClient } from '@/shared/http/HttpClient'
import type { User, UserRole } from '../domain/User'
import type { UserDirectory } from '../application/UserDirectory'
import { mapUser } from './userMapper'

export class UserHttpDirectory implements UserDirectory {
  constructor(private readonly http: HttpClient) {}

  async getById(id: string): Promise<User> {
    return mapUser(await this.http.get(`/users/${encodeURIComponent(id)}`))
  }

  async assignRole(id: string, role: UserRole): Promise<User> {
    return mapUser(await this.http.patch(`/users/${encodeURIComponent(id)}`, { role }))
  }
}
