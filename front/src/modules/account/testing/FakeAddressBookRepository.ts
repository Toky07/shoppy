import type { AddressBookRepository } from '../application/AddressBookRepository'
import type { SavedAddress } from '../domain/SavedAddress'

export class FakeAddressBookRepository implements AddressBookRepository {
  public addresses: SavedAddress[]
  public listCount = 0
  public removed: string[] = []
  public markedDefault: string[] = []

  constructor(addresses: SavedAddress[] = []) {
    this.addresses = addresses.map((address) => ({ ...address }))
  }

  async list(): Promise<SavedAddress[]> {
    this.listCount += 1
    return this.addresses.map((address) => ({ ...address }))
  }

  async remove(id: string): Promise<SavedAddress[]> {
    this.removed.push(id)
    this.addresses = this.addresses.filter((address) => address.id !== id)

    if (this.addresses.length > 0 && !this.addresses.some((address) => address.isDefault)) {
      this.addresses[0] = { ...this.addresses[0], isDefault: true }
    }

    return this.addresses.map((address) => ({ ...address }))
  }

  async markDefault(id: string): Promise<SavedAddress[]> {
    this.markedDefault.push(id)
    this.addresses = this.addresses.map((address) => ({
      ...address,
      isDefault: address.id === id,
    }))

    return this.addresses.map((address) => ({ ...address }))
  }
}
