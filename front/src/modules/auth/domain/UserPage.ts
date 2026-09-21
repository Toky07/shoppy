import type { User } from './User'

export type UserPage = {
  items: User[]
  page: number
  limit: number
  total: number
}
