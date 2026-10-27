import { computed, ref, watch } from 'vue'
import { applyDocumentLocale, INTL_LOCALES, type Locale } from './locale'
import { createMemoryLocaleStore, type LocaleStore } from './localeStore'
import { translateMessage } from './catalogs'

export function createLocaleState(store: LocaleStore = createMemoryLocaleStore('fr')) {
  const locale = ref<Locale>(store.read() ?? 'fr')

  function t(key: string, params?: Record<string, string | number>): string {
    return translateMessage(locale.value, key, params)
  }

  function setLocale(next: Locale) {
    locale.value = next
    store.write(next)
  }

  watch(
    locale,
    (current) => {
      applyDocumentLocale(current)
    },
    { immediate: true },
  )

  return {
    locale,
    intlLocale: computed(() => INTL_LOCALES[locale.value]),
    t,
    setLocale,
  }
}

export type LocaleState = ReturnType<typeof createLocaleState>
