<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from './AppIcon.vue'
import AppSearchForm from './AppSearchForm.vue'
import ThemeToggle from './ThemeToggle.vue'

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
        label="Rechercher un article"
        placeholder="Rechercher un article..."
        @submit="emit('search')"
      />

      <nav class="grid gap-1 text-sm font-semibold text-strong" aria-label="Navigation mobile">
        <RouterLink to="/" class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted">
          <AppIcon name="grid" :size="17" /> Le catalogue
        </RouterLink>
        <RouterLink
          to="/favorites"
          class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
        >
          <AppIcon name="heart" :size="17" /> Ma liste d'envies
        </RouterLink>
        <template v-if="isAuthenticated">
          <RouterLink
            to="/orders"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="package" :size="17" /> Mes achats
          </RouterLink>
          <RouterLink
            to="/account"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="user" :size="17" /> Mon profil
          </RouterLink>
          <RouterLink
            v-if="isAdmin"
            to="/admin"
            class="flex items-center gap-3 rounded-2xl px-3 py-3 hover:bg-surface-muted"
          >
            <AppIcon name="settings" :size="17" /> Administration
          </RouterLink>
        </template>
      </nav>

      <div class="flex items-center justify-between border-t border-line pt-4">
        <ThemeToggle />
        <template v-if="isAuthenticated">
          <button type="button" class="btn-outline btn-sm" @click="emit('logout')">
            <AppIcon name="logout" :size="15" /> Se déconnecter
          </button>
        </template>
        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="btn-outline btn-sm">Se connecter</RouterLink>
          <RouterLink to="/register" class="btn-primary btn-sm">Créer un compte</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
