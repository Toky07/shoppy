import type { PostalAddress } from '@/modules/order/domain/PostalAddress'

export type SavedAddress = PostalAddress & {
  id: string
  isDefault: boolean
}

export function formatSavedAddress(address: SavedAddress): string {
  return `${address.recipient} · ${address.line1}, ${address.postalCode} ${address.city}`
}
