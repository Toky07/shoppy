import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { UserPage } from '../domain/UserPage'
import type { ListUsersQuery, UserDirectory } from './UserDirectory'

export type UserListStatus = 'loading' | 'ready' | 'empty' | 'error'

export function useUserList(directory: UserDirectory, query: MaybeRefOrGetter<ListUsersQuery>) {
  const status = ref<UserListStatus>('loading')
  const page = ref<UserPage | null>(null)
  const error = ref<ApiError | null>(null)

  async function load() {
    status.value = 'loading'
    error.value = null

    try {
      const result = await directory.list(toValue(query))
      page.value = result
      status.value = result.items.length === 0 ? 'empty' : 'ready'
    } catch (caught) {
      page.value = null
      error.value = toApiError(caught)
      status.value = 'error'
    }
  }

  watch(() => toValue(query), load, { immediate: true, deep: true })

  return { status, page, error, reload: load }
}
