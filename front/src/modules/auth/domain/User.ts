export type UserRole = 'customer' | 'admin'

export type User = {
  id: string
  email: string
  role: UserRole
  createdAt: string
}
