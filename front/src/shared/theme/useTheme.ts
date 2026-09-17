import { computed, ref, watch } from 'vue'

export const THEME_PREFERENCES = ['light', 'dark', 'system'] as const

export type ThemePreference = (typeof THEME_PREFERENCES)[number]

const STORAGE_KEY = 'shoppy.theme'

function isThemePreference(value: unknown): value is ThemePreference {
  return typeof value === 'string' && (THEME_PREFERENCES as readonly string[]).includes(value)
}

function readStoredPreference(): ThemePreference {
  try {
    const stored = window.localStorage.getItem(STORAGE_KEY)
    return isThemePreference(stored) ? stored : 'system'
  } catch {
    return 'system'
  }
}

function systemPrefersDark(): boolean {
  try {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
  } catch {
    return false
  }
}

const preference = ref<ThemePreference>(readStoredPreference())
const systemDark = ref(systemPrefersDark())
const isDark = computed(() =>
  preference.value === 'system' ? systemDark.value : preference.value === 'dark',
)

function applyToDocument() {
  try {
    document.documentElement.classList.toggle('dark', isDark.value)
  } catch {
    /* pas de DOM : rien à appliquer */
  }
}

let listening = false

function listenToSystem() {
  if (listening) {
    return
  }

  try {
    const query = window.matchMedia('(prefers-color-scheme: dark)')
    query.addEventListener('change', (event) => {
      systemDark.value = event.matches
    })
    listening = true
  } catch {
    /* matchMedia indisponible */
  }
}

watch(isDark, applyToDocument, { immediate: true })

/** Thème clair / sombre / système, mémorisé entre les visites. */
export function useTheme() {
  listenToSystem()

  function setPreference(next: ThemePreference) {
    preference.value = next

    try {
      window.localStorage.setItem(STORAGE_KEY, next)
    } catch {
      /* stockage indisponible (navigation privée) */
    }
  }

  function toggle() {
    setPreference(isDark.value ? 'light' : 'dark')
  }

  return { preference, isDark, setPreference, toggle }
}
