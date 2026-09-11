import { ref } from 'vue'
import { describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/vue'
import { ApiError } from '@/shared/http/ApiError'
import { createCartState } from '../createCartState'
import { FakeCartRepository } from '../../testing/FakeCartRepository'
import { emptyCart, filledCart, pendingCheckout } from '../../testing/cartFixtures'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'

describe('createCartState', () => {
  it('does not load the cart when logged out', async () => {
    const repository = new FakeCartRepository(filledCart)
    const state = createCartState(repository, ref(false))

    await waitFor(() => {
      expect(state.loading.value).toBe(false)
    })
    expect(repository.getCount).toBe(0)
    expect(state.cart.value).toBeNull()
    expect(state.itemCount.value).toBe(0)
  })

  it('loads the cart when authenticated', async () => {
    const repository = new FakeCartRepository(filledCart)
    const state = createCartState(repository, ref(true))

    await waitFor(() => {
      expect(state.cart.value).toEqual(filledCart)
    })
    expect(state.itemCount.value).toBe(2)
  })

  it('clears the cart when the user logs out', async () => {
    const repository = new FakeCartRepository(filledCart)
    const isAuthenticated = ref(true)
    const state = createCartState(repository, isAuthenticated)

    await waitFor(() => {
      expect(state.cart.value).toEqual(filledCart)
    })

    isAuthenticated.value = false

    await waitFor(() => {
      expect(state.cart.value).toBeNull()
      expect(state.itemCount.value).toBe(0)
    })
  })

  it('stores a load error', async () => {
    const repository = new FakeCartRepository()
    repository.get = async () => {
      throw new ApiError(500, 'internal_error', 'Boom.')
    }
    const state = createCartState(repository, ref(true))

    await waitFor(() => {
      expect(state.error.value?.message).toBe('Boom.')
      expect(state.cart.value).toBeNull()
    })
  })

  it('updates local cart after mutations', async () => {
    const repository = new FakeCartRepository(emptyCart())
    const state = createCartState(repository, ref(true))

    await waitFor(() => {
      expect(state.cart.value?.items).toEqual([])
    })

    await state.addItem(nuvoraTee.id, 1)
    expect(state.itemCount.value).toBe(1)

    await state.updateItem(nuvoraTee.id, 4)
    expect(state.itemCount.value).toBe(4)

    await state.removeItem(nuvoraTee.id)
    expect(state.itemCount.value).toBe(0)
  })

  it('refreshes an empty cart after checkout', async () => {
    const repository = new FakeCartRepository(filledCart)
    const state = createCartState(repository, ref(true))

    await waitFor(() => {
      expect(state.itemCount.value).toBe(2)
    })

    await expect(state.checkout()).resolves.toEqual(pendingCheckout)
    expect(state.cart.value?.items).toEqual([])
    expect(repository.checkoutCount).toBe(1)
  })
})
