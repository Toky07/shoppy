<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import ThemeToggle from '@/shared/ui/ThemeToggle.vue'
import { useFavorites } from '@/modules/catalog/application/useFavorites'

const { count: favoriteCount, clear: clearFavorites } = useFavorites()
</script>

<template>
  <div class="panel p-6 sm:p-7">
    <h2 class="font-display text-lg font-bold text-strong">Préférences</h2>

    <div class="mt-5 flex items-center justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-strong">Apparence</p>
        <p class="mt-1 text-xs text-muted">Clair, sombre ou selon votre système.</p>
      </div>
      <ThemeToggle />
    </div>

    <div class="mt-6 flex items-center justify-between gap-4 border-t border-line pt-6">
      <div>
        <p class="text-sm font-semibold text-strong">Liste d'envies</p>
        <p class="mt-1 text-xs text-muted">Gardée sur cet appareil, jamais envoyée.</p>
      </div>
      <button
        type="button"
        class="btn-outline btn-sm shrink-0"
        :disabled="favoriteCount === 0"
        @click="clearFavorites"
      >
        <AppIcon name="trash" :size="14" />
        Vider
      </button>
    </div>
  </div>
</template>
