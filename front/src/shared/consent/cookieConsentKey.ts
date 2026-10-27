import type { InjectionKey } from 'vue'
import type { CookieConsentState } from './createCookieConsentState'

export const cookieConsentKey: InjectionKey<CookieConsentState> = Symbol('cookieConsent')
