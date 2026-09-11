import type { User, UserRole } from '../domain/User'

export interface UserDirectory {
  getById(id: string): Promise<User>
  assignRole(id: string, role: UserRole): Promise<User>
}
