import { computed, ref } from 'vue'
import {
  createMemoryCookieConsentStore,
  type CookieChoice,
  type CookieConsentStore,
} from './cookieConsentStore'

export function createCookieConsentState(
  store: CookieConsentStore = createMemoryCookieConsentStore(),
) {
  const choice = ref<CookieChoice | null>(store.read())
  const open = ref(choice.value === null)

  function persist(next: CookieChoice) {
    store.write(next)
    choice.value = next
    open.value = false
  }

  function acceptAll() {
    persist('all')
  }

  function refuseOptional() {
    persist('necessary')
  }

  function review() {
    open.value = true
  }

  return {
    choice,
    visible: computed(() => open.value),
    allowsAnalytics: computed(() => choice.value === 'all'),
    acceptAll,
    refuseOptional,
    review,
  }
}

export type CookieConsentState = ReturnType<typeof createCookieConsentState>
