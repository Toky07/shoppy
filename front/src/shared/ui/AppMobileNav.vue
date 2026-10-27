<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from './AppIcon.vue'
import AppSearchForm from './AppSearchForm.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import ThemeToggle from './ThemeToggle.vue'

const { t } = useI18n()

defineProps<{
  isAuthenticated: boolean
  isAdmin: boolean
}>()

const search = defineModel<string>('search', { required: true })

const emit = defineEmits<{
  search: []
  logout: []
}>()
</script>

<template>
  <div class="border-t border-line bg-surface lg:hidden">
    <div class="mx-auto max-w-7xl space-y-5 px-5 py-5">
      <AppSearchForm
        id="mobile-search"
        v-model="search"
        :label="t('header.mobileSearchLabel')"
        :placeholder="t('header.mobileSearchPlaceholder')"
        @submit="emit('search')"
      />

      <nav class="grid gap-1 text-sm font-semibold text-strong" :aria-label="t('nav.mobile')">
        <RouterLink to="/" class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted">
          <AppIcon name="grid" :size="17" /> {{ t('mobile.catalog') }}
        </RouterLink>
        <RouterLink
          to="/favorites"
          class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
        >
          <AppIcon name="heart" :size="17" /> {{ t('mobile.wishlist') }}
        </RouterLink>
        <template v-if="isAuthenticated">
          <RouterLink
            to="/orders"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="package" :size="17" /> {{ t('mobile.orders') }}
          </RouterLink>
          <RouterLink
            to="/account"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="user" :size="17" /> {{ t('mobile.profile') }}
          </RouterLink>
          <RouterLink
            v-if="isAdmin"
            to="/admin"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="settings" :size="17" /> {{ t('mobile.admin') }}
          </RouterLink>
        </template>
      </nav>

      <div class="flex items-center justify-between gap-3 border-t border-line pt-4">
        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <ThemeToggle />
        </div>
        <template v-if="isAuthenticated">
          <button type="button" class="btn-outline btn-sm" @click="emit('logout')">
            <AppIcon name="logout" :size="15" /> {{ t('mobile.logout') }}
          </button>
        </template>
        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="btn-outline btn-sm">{{ t('mobile.login') }}</RouterLink>
          <RouterLink to="/register" class="btn-primary btn-sm">{{ t('mobile.register') }}</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
