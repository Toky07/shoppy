<script setup lang="ts">
import { inject } from 'vue'
import { RouterLink } from 'vue-router'
import { cookieConsentKey } from '@/shared/consent/cookieConsentKey'

const consent = inject(cookieConsentKey)

if (!consent) {
  throw new Error('CookieConsent is not provided.')
}

const { visible, acceptAll, refuseOptional } = consent
</script>

<template>
  <div
    v-if="visible"
    role="dialog"
    aria-modal="false"
    aria-labelledby="cookie-banner-title"
    aria-describedby="cookie-banner-text"
    class="fixed inset-x-0 bottom-0 z-60 border-t border-line bg-surface/95 px-5 py-5 shadow-lifted backdrop-blur-md lg:px-6"
  >
    <div class="mx-auto flex max-w-7xl flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
      <div class="max-w-2xl">
        <h2 id="cookie-banner-title" class="text-sm font-semibold text-strong">Cookies</h2>
        <p id="cookie-banner-text" class="mt-2 text-sm leading-relaxed text-muted">
          Les cookies nécessaires au compte, au panier et à la sécurité sont déjà en place. Aucun
          cookie de mesure d'audience n'est déposé tant que vous n'acceptez pas.
          <RouterLink to="/confidentialite" class="font-semibold link-quiet">
            Politique de confidentialité
          </RouterLink>
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="btn-outline" @click="refuseOptional">Refuser</button>
        <button type="button" class="btn-primary" @click="acceptAll">Accepter</button>
      </div>
    </div>
  </div>
</template>
