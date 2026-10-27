import type { HttpClient } from '@/shared/http/HttpClient'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapMoney } from '@/shared/money/mapMoney'
import { isRecord } from '@/shared/types/isRecord'
import type { AdminDashboardRepository } from '../application/AdminDashboardRepository'
import type { AdminDashboard, LowStockProduct } from '../domain/AdminDashboard'

export class AdminDashboardHttpRepository implements AdminDashboardRepository {
  constructor(private readonly http: HttpClient) {}

  async get(): Promise<AdminDashboard> {
    return mapAdminDashboard(await this.http.get('/admin/dashboard'))
  }
}

function mapAdminDashboard(payload: unknown): AdminDashboard {
  if (!isRecord(payload) || !isRecord(payload.today) || typeof payload.today.orders !== 'number') {
    throw new InvalidResponseError('Invalid dashboard payload.')
  }

  return {
    revenue: mapMoney(payload.revenue, 'Invalid dashboard payload.'),
    today: {
      orders: payload.today.orders,
      revenue: mapMoney(payload.today.revenue, 'Invalid dashboard payload.'),
    },
    lowStock: mapLowStock(payload.lowStock),
  }
}

function mapLowStock(payload: unknown): LowStockProduct[] {
  if (!Array.isArray(payload)) {
    throw new InvalidResponseError('Invalid dashboard payload.')
  }

  return payload.map((item) => {
    if (
      !isRecord(item) ||
      typeof item.id !== 'string' ||
      typeof item.name !== 'string' ||
      typeof item.slug !== 'string' ||
      typeof item.stock !== 'number'
    ) {
      throw new InvalidResponseError('Invalid dashboard payload.')
    }

    return { id: item.id, name: item.name, slug: item.slug, stock: item.stock }
  })
}
