export type PostalAddress = {
  recipient: string
  line1: string
  line2: string | null
  postalCode: string
  city: string
  country: string
}

export type AddressDraft = {
  recipient: string
  line1: string
  line2: string
  postalCode: string
  city: string
  country: string
}

export function emptyAddressDraft(): AddressDraft {
  return {
    recipient: '',
    line1: '',
    line2: '',
    postalCode: '',
    city: '',
    country: 'FR',
  }
}

export function toPostalAddress(draft: AddressDraft): PostalAddress {
  return {
    recipient: draft.recipient.trim(),
    line1: draft.line1.trim(),
    line2: draft.line2.trim() === '' ? null : draft.line2.trim(),
    postalCode: draft.postalCode.trim(),
    city: draft.city.trim(),
    country: draft.country.trim().toUpperCase(),
  }
}
