import { ref } from 'vue'
import { describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/vue'
import { createAddressBookState } from '../createAddressBookState'
import { FakeAddressBookRepository } from '../../testing/FakeAddressBookRepository'
import { lyonAddress, parisAddress } from '../../testing/addressFixtures'

describe('createAddressBookState', () => {
  it('keeps the book empty while logged out', async () => {
    const repository = new FakeAddressBookRepository([parisAddress])
    const state = createAddressBookState(repository, ref(false))

    await waitFor(() => {
      expect(state.addresses.value).toEqual([])
    })
    expect(repository.listCount).toBe(0)
  })

  it('loads account addresses when authenticated', async () => {
    const repository = new FakeAddressBookRepository([parisAddress, lyonAddress])
    const state = createAddressBookState(repository, ref(true))

    await waitFor(() => {
      expect(state.addresses.value).toEqual([parisAddress, lyonAddress])
      expect(state.defaultAddress.value).toEqual(parisAddress)
    })
  })

  it('reloads after login and can remove or mark a default', async () => {
    const repository = new FakeAddressBookRepository([parisAddress, lyonAddress])
    const isAuthenticated = ref(false)
    const state = createAddressBookState(repository, isAuthenticated)

    isAuthenticated.value = true

    await waitFor(() => {
      expect(state.addresses.value).toHaveLength(2)
    })

    await state.markDefault(lyonAddress.id)
    expect(repository.markedDefault).toEqual([lyonAddress.id])
    expect(state.defaultAddress.value?.id).toBe(lyonAddress.id)

    await state.remove(lyonAddress.id)
    expect(repository.removed).toEqual([lyonAddress.id])
    expect(state.addresses.value).toEqual([{ ...parisAddress, isDefault: true }])
  })
})
