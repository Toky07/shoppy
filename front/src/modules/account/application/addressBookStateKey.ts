import type { InjectionKey } from 'vue'
import type { AddressBookState } from './createAddressBookState'

export const addressBookStateKey: InjectionKey<AddressBookState> = Symbol('addressBookState')
