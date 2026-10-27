import type { HttpClient } from '@/shared/http/HttpClient'
import type { AddressBookRepository } from '../application/AddressBookRepository'
import type { SavedAddress } from '../domain/SavedAddress'

type AddressBookJson = {
  addresses?: unknown
}

function addresses(payload: AddressBookJson): SavedAddress[] {
  return Array.isArray(payload.addresses)
    ? payload.addresses.filter((item): item is SavedAddress => isSavedAddress(item))
    : []
}

function isSavedAddress(value: unknown): value is SavedAddress {
  if (typeof value !== 'object' || value === null) {
    return false
  }

  const item = value as Record<string, unknown>

  return (
    typeof item.id === 'string' &&
    typeof item.recipient === 'string' &&
    typeof item.line1 === 'string' &&
    (item.line2 === null || typeof item.line2 === 'string') &&
    typeof item.postalCode === 'string' &&
    typeof item.city === 'string' &&
    typeof item.country === 'string' &&
    typeof item.isDefault === 'boolean'
  )
}

export class AddressHttpRepository implements AddressBookRepository {
  constructor(private readonly http: HttpClient) {}

  async list(): Promise<SavedAddress[]> {
    return addresses(await this.http.get<AddressBookJson>('/addresses'))
  }

  async remove(id: string): Promise<SavedAddress[]> {
    return addresses(await this.http.delete<AddressBookJson>(`/addresses/${encodeURIComponent(id)}`))
  }

  async markDefault(id: string): Promise<SavedAddress[]> {
    return addresses(
      await this.http.post<AddressBookJson>(`/addresses/${encodeURIComponent(id)}/default`),
    )
  }
}
