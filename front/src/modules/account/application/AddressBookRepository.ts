import type { SavedAddress } from '../domain/SavedAddress'

export interface AddressBookRepository {
  list(): Promise<SavedAddress[]>
  remove(id: string): Promise<SavedAddress[]>
  markDefault(id: string): Promise<SavedAddress[]>
}
