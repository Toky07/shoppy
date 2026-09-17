<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { useFavorites } from '@/modules/catalog/application/useFavorites'
import { authSessionKey } from '../application/authSessionKey'
import { accountShortcuts } from './accountShortcuts'

const session = inject(authSessionKey)
const cartState = inject(cartStateKey)
const { count: favoriteCount } = useFavorites()

const isAdmin = computed(() => session?.session.value?.user.role === 'admin')
const cartCount = computed(() => cartState?.itemCount.value ?? 0)
const shortcuts = computed(() => accountShortcuts(favoriteCount.value, cartCount.value))
</script>

<template>
  <div class="panel p-6 sm:p-7">
    <h2 class="font-display text-lg font-bold text-strong">Mon activité</h2>
    <div class="mt-5 grid gap-2">
      <RouterLink
        v-for="shortcut in shortcuts"
        :key="shortcut.to"
        :to="shortcut.to"
        class="group flex items-center gap-4 rounded-2xl border border-transparent px-3 py-3 transition-colors hover:border-line hover:bg-surface-muted"
      >
        <span
          class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-line bg-surface-inset text-body transition-colors group-hover:text-accent-strong"
        >
          <AppIcon :name="shortcut.icon" :size="18" />
        </span>
        <span class="min-w-0 flex-1">
          <span class="block text-sm font-semibold text-strong">{{ shortcut.label }}</span>
          <span class="block truncate text-xs text-muted">{{ shortcut.detail }}</span>
        </span>
        <AppIcon
          name="arrow-right"
          :size="16"
          class="text-faint transition-transform duration-300 group-hover:translate-x-1"
        />
      </RouterLink>

      <RouterLink
        v-if="isAdmin"
        to="/admin"
        class="group flex items-center gap-4 rounded-2xl border border-transparent px-3 py-3 transition-colors hover:border-line hover:bg-surface-muted"
      >
        <span
          class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-line bg-accent-soft text-accent-fg"
        >
          <AppIcon name="settings" :size="18" />
        </span>
        <span class="min-w-0 flex-1">
          <span class="block text-sm font-semibold text-strong">Administration</span>
          <span class="block truncate text-xs text-muted">Commandes, catalogue et accès</span>
        </span>
        <AppIcon
          name="arrow-right"
          :size="16"
          class="text-faint transition-transform duration-300 group-hover:translate-x-1"
        />
      </RouterLink>
    </div>
  </div>
</template>
