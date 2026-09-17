<script setup lang="ts">
import AppFooter from './AppFooter.vue'
import AppHeader from './AppHeader.vue'
import AppTrustBanner from './AppTrustBanner.vue'
import BackToTop from './BackToTop.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col bg-canvas">
    <a
      href="#main"
      class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-60 focus:rounded-full focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:text-primary-fg"
    >
      Aller au contenu
    </a>

    <AppTrustBanner />
    <AppHeader />

    <main id="main" class="mx-auto w-full max-w-7xl flex-1 px-5 py-10 lg:px-6 lg:py-14">
      <slot />
    </main>

    <AppFooter />
    <BackToTop />
  </div>
</template>
