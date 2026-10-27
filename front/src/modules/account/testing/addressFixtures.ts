import type { SavedAddress } from '../domain/SavedAddress'

export const parisAddress: SavedAddress = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  recipient: 'Ada Lovelace',
  line1: '10 rue de la Paix',
  line2: null,
  postalCode: '75002',
  city: 'Paris',
  country: 'FR',
  isDefault: true,
}

export const lyonAddress: SavedAddress = {
  id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
  recipient: 'Ada Lovelace',
  line1: '2 place Bellecour',
  line2: null,
  postalCode: '69002',
  city: 'Lyon',
  country: 'FR',
  isDefault: false,
}
