import type { HttpClient } from '@/shared/http/HttpClient'
import type { User, UserRole } from '../domain/User'
import type { ListUsersQuery, UserDirectory } from '../application/UserDirectory'
import type { UserPage } from '../domain/UserPage'
import { mapUser, mapUserPage } from './userMapper'

export class UserHttpDirectory implements UserDirectory {
  constructor(private readonly http: HttpClient) {}

  async list(query: ListUsersQuery): Promise<UserPage> {
    return mapUserPage(
      await this.http.get('/admin/users', {
        page: query.page,
        limit: query.limit,
        q: query.search,
      }),
    )
  }

  async getById(id: string): Promise<User> {
    return mapUser(await this.http.get(`/users/${encodeURIComponent(id)}`))
  }

  async assignRole(id: string, role: UserRole): Promise<User> {
    return mapUser(await this.http.patch(`/users/${encodeURIComponent(id)}`, { role }))
  }
}
