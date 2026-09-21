import type { User, UserRole } from '../domain/User'
import type { UserPage } from '../domain/UserPage'

export type ListUsersQuery = {
  page: number
  limit: number
  search?: string
}

export interface UserDirectory {
  list(query: ListUsersQuery): Promise<UserPage>
  getById(id: string): Promise<User>
  assignRole(id: string, role: UserRole): Promise<User>
}
