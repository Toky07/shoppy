import { ref } from 'vue'
import type { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'

export function usePendingAction(toMessage: (error: ApiError) => string = (error) => error.message) {
  const pending = ref(false)
  const errorMessage = ref<string>()

  async function run(action: () => Promise<unknown>) {
    pending.value = true
    errorMessage.value = undefined

    try {
      await action()
    } catch (caught) {
      errorMessage.value = toMessage(toApiError(caught))
    } finally {
      pending.value = false
    }
  }

  return { pending, errorMessage, run }
}
