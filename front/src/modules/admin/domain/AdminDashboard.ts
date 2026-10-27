import type { Money } from '@/shared/money/Money'

export type LowStockProduct = {
  id: string
  name: string
  slug: string
  stock: number
}

export type AdminDashboard = {
  revenue: Money
  today: {
    orders: number
    revenue: Money
  }
  lowStock: LowStockProduct[]
}
