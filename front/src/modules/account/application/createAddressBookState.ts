import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import type { AddressBookRepository } from './AddressBookRepository'
import type { SavedAddress } from '../domain/SavedAddress'

export function createAddressBookState(
  repository: AddressBookRepository,
  isAuthenticated: MaybeRefOrGetter<boolean>,
) {
  const addresses = ref<SavedAddress[]>([])
  const loading = ref(false)

  async function refresh() {
    if (!toValue(isAuthenticated)) {
      addresses.value = []
      loading.value = false
      return
    }

    loading.value = true

    try {
      addresses.value = await repository.list()
    } catch {
      addresses.value = []
    } finally {
      loading.value = false
    }
  }

  watch(() => toValue(isAuthenticated), refresh, { immediate: true })

  return {
    addresses: computed(() => addresses.value),
    loading: computed(() => loading.value),
    defaultAddress: computed(
      () => addresses.value.find((address) => address.isDefault) ?? addresses.value[0],
    ),
    refresh,
    remove: (id: string) =>
      repository.remove(id).then((next) => {
        addresses.value = next
      }),
    markDefault: (id: string) =>
      repository.markDefault(id).then((next) => {
        addresses.value = next
      }),
  }
}

export type AddressBookState = ReturnType<typeof createAddressBookState>
