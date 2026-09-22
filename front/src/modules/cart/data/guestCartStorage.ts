import type { Cart } from '../domain/Cart'
import type { CartItem } from '../domain/CartItem'

export type GuestCartLine = {
  productId: string
  variantId: string | null
  quantity: number
  name: string
  unitPriceCents: number
  availableStock: number
}

export type GuestCartStore = {
  read(): GuestCartLine[]
  write(lines: GuestCartLine[]): void
  clear(): void
}

const storageKey = 'shoppy.guestCart'

export function createMemoryGuestCart(): GuestCartStore {
  let lines: GuestCartLine[] = []

  return {
    read: () => lines.map((line) => ({ ...line })),
    write: (next) => {
      lines = next.map((line) => ({ ...line }))
    },
    clear: () => {
      lines = []
    },
  }
}

export function createLocalGuestCart(storage: Storage): GuestCartStore {
  return {
    read: () => {
      const raw = storage.getItem(storageKey)
      if (!raw) {
        return []
      }

      try {
        const parsed = JSON.parse(raw) as unknown
        return Array.isArray(parsed) ? parsed.filter(isGuestLine) : []
      } catch {
        return []
      }
    },
    write: (lines) => {
      storage.setItem(storageKey, JSON.stringify(lines))
    },
    clear: () => {
      storage.removeItem(storageKey)
    },
  }
}

export function guestCartFromLines(lines: GuestCartLine[]): Cart | null {
  if (lines.length === 0) {
    return null
  }

  const items: CartItem[] = lines.map((line) => ({
    productId: line.productId,
    variantId: line.variantId,
    name: line.name,
    quantity: line.quantity,
    unitPrice: { cents: line.unitPriceCents, currency: 'EUR' },
    lineTotal: { cents: line.unitPriceCents * line.quantity, currency: 'EUR' },
    availableStock: line.availableStock,
  }))

  return {
    id: null,
    customerId: 'guest',
    items,
    total: {
      cents: items.reduce((sum, item) => sum + item.lineTotal.cents, 0),
      currency: 'EUR',
    },
    updatedAt: null,
  }
}

function isGuestLine(value: unknown): value is GuestCartLine {
  if (!value || typeof value !== 'object') {
    return false
  }

  const line = value as Partial<GuestCartLine>
  return (
    typeof line.productId === 'string' &&
    (line.variantId === null || typeof line.variantId === 'string') &&
    typeof line.quantity === 'number' &&
    typeof line.name === 'string' &&
    typeof line.unitPriceCents === 'number' &&
    typeof line.availableStock === 'number'
  )
}
