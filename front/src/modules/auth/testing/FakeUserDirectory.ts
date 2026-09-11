import { ApiError } from '@/shared/http/ApiError'
import type { User, UserRole } from '../domain/User'
import type { UserDirectory } from '../application/UserDirectory'
import { visitorUser } from './authFixtures'

export class FakeUserDirectory implements UserDirectory {
  public users: User[]
  public assigned: Array<{ id: string; role: UserRole }> = []
  public assignError: Error | null = null

  constructor(users: User[] = [visitorUser]) {
    this.users = users.map((user) => ({ ...user }))
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
