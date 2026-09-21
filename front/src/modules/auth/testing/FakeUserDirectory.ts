import { ApiError } from '@/shared/http/ApiError'
import type { ListUsersQuery, UserDirectory } from '../application/UserDirectory'
import type { User, UserRole } from '../domain/User'
import type { UserPage } from '../domain/UserPage'
import { visitorUser } from './authFixtures'

export class FakeUserDirectory implements UserDirectory {
  public users: User[]
  public assigned: Array<{ id: string; role: UserRole }> = []
  public listCount = 0
  public assignError: Error | null = null

  constructor(users: User[] = [visitorUser]) {
    this.users = users.map((user) => ({ ...user }))
  }

  async list(query: ListUsersQuery): Promise<UserPage> {
    this.listCount += 1
    const needle = query.search?.trim().toLowerCase()
    const filtered = this.users.filter((user) => (needle ? user.email.includes(needle) : true))
    const start = (query.page - 1) * query.limit

    return {
      items: filtered.slice(start, start + query.limit).map((user) => ({ ...user })),
      page: query.page,
      limit: query.limit,
      total: filtered.length,
    }
  }

  async getById(id: string): Promise<User> {
    const user = this.users.find((item) => item.id === id)
    if (!user) {
      throw new ApiError(404, 'user_not_found', 'User not found.')
    }
    return { ...user }
  }

  async assignRole(id: string, role: UserRole): Promise<User> {
    this.assigned.push({ id, role })
    if (this.assignError) {
      throw this.assignError
    }
    const user = await this.getById(id)
    const next = { ...user, role }
    this.users = this.users.map((item) => (item.id === id ? next : item))
    return { ...next }
  }
}
