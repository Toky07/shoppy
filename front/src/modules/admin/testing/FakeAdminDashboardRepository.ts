import type { AdminDashboardRepository } from '../application/AdminDashboardRepository'
import type { AdminDashboard } from '../domain/AdminDashboard'

export const emptyAdminDashboard: AdminDashboard = {
  revenue: { cents: 0, currency: 'EUR' },
  today: { orders: 0, revenue: { cents: 0, currency: 'EUR' } },
  lowStock: [],
}

export class FakeAdminDashboardRepository implements AdminDashboardRepository {
  public dashboard: AdminDashboard
  public getCount = 0
  public getError: Error | null = null

  constructor(dashboard: AdminDashboard = emptyAdminDashboard) {
    this.dashboard = dashboard
  }

  async get(): Promise<AdminDashboard> {
    this.getCount += 1
    if (this.getError) {
      throw this.getError
    }

    return {
      revenue: { ...this.dashboard.revenue },
      today: {
        orders: this.dashboard.today.orders,
        revenue: { ...this.dashboard.today.revenue },
      },
      lowStock: this.dashboard.lowStock.map((product) => ({ ...product })),
    }
  }
}
