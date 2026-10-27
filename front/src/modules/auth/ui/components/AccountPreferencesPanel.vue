<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import ThemeToggle from '@/shared/ui/ThemeToggle.vue'
import { useI18n } from '@/shared/i18n/useI18n'
import { useFavorites } from '@/modules/catalog/application/useFavorites'

const { t } = useI18n()
const { count: favoriteCount, clear: clearFavorites } = useFavorites()
</script>

<template>
  <div class="panel p-6 sm:p-7">
    <h2 class="font-display text-lg font-bold text-strong">{{ t('account.preferences') }}</h2>

    <div class="mt-5 flex items-center justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-strong">{{ t('account.appearance') }}</p>
        <p class="mt-1 text-xs text-muted">{{ t('account.appearanceHint') }}</p>
      </div>
      <ThemeToggle />
    </div>

    <div class="mt-6 flex items-center justify-between gap-4 border-t border-line pt-6">
      <div>
        <p class="text-sm font-semibold text-strong">{{ t('account.wishlist') }}</p>
        <p class="mt-1 text-xs text-muted">{{ t('account.wishlistHint') }}</p>
      </div>
      <button
        type="button"
        class="btn-outline btn-sm shrink-0"
        :disabled="favoriteCount === 0"
        @click="clearFavorites"
      >
        <AppIcon name="trash" :size="14" />
        {{ t('account.clear') }}
      </button>
    </div>
  </div>
</template>
