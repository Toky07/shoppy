<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { logout } from '@/modules/auth/application/logout'
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'
import ThemeToggle from '@/shared/ui/ThemeToggle.vue'
import { userInitials } from '@/shared/text/userInitials'
import { ADMIN_NAV, adminPageTitle, isAdminNavActive } from './adminNav'

const session = inject(authSessionKey)
const repository = inject(authRepositoryKey)
const router = useRouter()
const route = useRoute()

if (!session || !repository) {
  throw new Error('Admin layout dependencies are not provided.')
}

const authSession = session
const authRepository = repository
const sidebarOpen = ref(false)
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const isAdmin = computed(() => authSession.isAdmin.value)
const email = computed(() => authSession.session.value?.user.email ?? '')
const initials = computed(() => userInitials(email.value))
const pageTitle = computed(() => adminPageTitle(route.path))

watch(
  isAuthenticated,
  (authenticated) => {
    if (!authenticated) {
      void router.replace({
        name: 'admin-login',
        query: { redirect: route.fullPath },
      })
    }
  },
  { immediate: true },
)

watch(
  () => route.fullPath,
  () => {
    sidebarOpen.value = false
  },
)

onMounted(() => {
  document.title = 'Console — Shoppy'
})

onBeforeUnmount(() => {
  document.title = 'Shoppy — Boutique'
})

async function onLogout() {
  await logout(authRepository, authSession)
  await router.push({ name: 'admin-login' })
}
</script>

<template>
  <div v-if="!isAuthenticated" class="flex min-h-screen items-center justify-center bg-canvas">
    <p class="text-sm font-medium text-muted">Redirection vers la connexion…</p>
  </div>

  <div
    v-else-if="!isAdmin"
    class="flex min-h-screen flex-col items-center justify-center bg-canvas px-5 text-center"
  >
    <div class="flex size-14 items-center justify-center rounded-2xl bg-danger-soft text-danger">
      <AppIcon name="shield" :size="24" />
    </div>
    <h1 class="mt-6 font-display text-2xl font-bold text-strong">Accès refusé</h1>
    <p role="alert" class="mt-3 max-w-sm text-sm text-muted">
      Cette page est réservée aux administrateurs.
    </p>
    <RouterLink to="/" class="btn-primary mt-8">Retour à la boutique</RouterLink>
  </div>

  <div v-else class="admin-root flex min-h-screen bg-canvas">
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-strong/40 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <aside
      class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 flex-col border-r border-transparent transition-transform duration-200 lg:static lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex items-center gap-3 px-5 py-5">
        <span class="text-white">
          <BrandMark :size="32" />
        </span>
        <div class="min-w-0">
          <p class="font-display text-lg font-extrabold tracking-tight text-white">Shoppy</p>
          <p class="text-[0.65rem] font-semibold tracking-[0.18em] text-white/45 uppercase">
            Console
          </p>
        </div>
      </div>

      <nav class="mt-2 flex-1 space-y-1 px-3" aria-label="Navigation administration">
        <RouterLink
          v-for="item in ADMIN_NAV"
          :key="item.to"
          :to="item.to"
          class="admin-nav-item"
          :class="isAdminNavActive(route.path, item) && 'admin-nav-active'"
        >
          <AppIcon :name="item.icon" :size="17" />
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="border-t px-3 py-4" style="border-color: var(--admin-ink-line)">
        <RouterLink to="/" class="admin-nav-item">
          <AppIcon name="globe" :size="17" />
          Voir la boutique
        </RouterLink>
      </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
      <header
        class="sticky top-0 z-50 flex h-16 items-center gap-3 border-b border-line bg-canvas/90 px-4 backdrop-blur-xl sm:px-6"
      >
        <button
          type="button"
          class="btn-icon lg:hidden"
          :aria-label="sidebarOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
          :aria-expanded="sidebarOpen"
          @click="sidebarOpen = !sidebarOpen"
        >
          <AppIcon :name="sidebarOpen ? 'close' : 'menu'" :size="20" />
        </button>

        <div class="min-w-0">
          <p class="text-[0.65rem] font-semibold tracking-[0.16em] text-faint uppercase">Console</p>
          <p class="truncate font-display text-lg font-bold text-strong">{{ pageTitle }}</p>
        </div>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
          <ThemeToggle />
          <div
            class="hidden items-center gap-2 rounded-full border border-line bg-surface py-1 pr-3 pl-1 sm:flex"
          >
            <span
              class="flex size-7 items-center justify-center rounded-full bg-accent-soft text-[0.65rem] font-bold text-accent-fg"
              aria-hidden="true"
            >
              {{ initials }}
            </span>
            <span class="max-w-40 truncate text-xs font-medium text-body">{{ email }}</span>
          </div>
          <button type="button" class="btn-ghost btn-sm" @click="onLogout">
            <AppIcon name="logout" :size="15" />
            <span class="hidden sm:inline">Déconnexion</span>
          </button>
        </div>
      </header>

      <main id="main" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
