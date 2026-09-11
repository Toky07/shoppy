import type { User } from './User'

export type Session = {
  accessToken: string
  user: User
}
