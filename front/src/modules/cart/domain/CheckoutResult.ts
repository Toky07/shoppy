import type { User } from '@/modules/auth/domain/User'

export type CheckoutResult = {
  id: string
  status: string
  user?: User
}
