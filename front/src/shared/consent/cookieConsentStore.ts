export const COOKIE_CONSENT_KEY = 'shoppy.cookie-consent'

export const COOKIE_CHOICES = ['necessary', 'all'] as const

export type CookieChoice = (typeof COOKIE_CHOICES)[number]

export type CookieConsentStore = {
  read(): CookieChoice | null
  write(choice: CookieChoice): void
}

function isCookieChoice(value: unknown): value is CookieChoice {
  return value === 'necessary' || value === 'all'
}

export function createMemoryCookieConsentStore(
  choice: CookieChoice | null = null,
): CookieConsentStore {
  let value = choice

  return {
    read: () => value,
    write: (next) => {
      value = next
    },
  }
}

export function createLocalCookieConsentStore(storage: Storage): CookieConsentStore {
  return {
    read() {
      try {
        const stored = storage.getItem(COOKIE_CONSENT_KEY)
        return isCookieChoice(stored) ? stored : null
      } catch {
        return null
      }
    },
    write(choice) {
      try {
        storage.setItem(COOKIE_CONSENT_KEY, choice)
      } catch {
        /* stockage indisponible (navigation privée) */
      }
    },
  }
}
