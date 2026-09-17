<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { logout } from '@/modules/auth/application/logout'
import { userRoleLabel } from '@/modules/auth/ui/userRoleLabel'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { useFavorites } from '@/modules/catalog/application/useFavorites'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import AccountMenu from './AccountMenu.vue'
import AppIcon from './AppIcon.vue'
import AppMobileNav from './AppMobileNav.vue'
import AppSearchForm from './AppSearchForm.vue'
import BrandMark from './BrandMark.vue'
import ThemeToggle from './ThemeToggle.vue'

const session = inject(authSessionKey)
const repository = inject(authRepositoryKey)
const cartState = inject(cartStateKey)
const router = useRouter()
const route = useRoute()
const { count: favoriteCount } = useFavorites()

const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const isAdmin = computed(() => session?.isAdmin.value ?? false)
const email = computed(() => session?.session.value?.user.email ?? '')
const roleLabel = computed(() => {
  const user = session?.session.value?.user

  return user ? userRoleLabel(user.role) : ''
})
const itemCount = computed(() => cartState?.itemCount.value ?? 0)
const cartLabel = computed(() => (itemCount.value > 0 ? `Panier (${itemCount.value})` : 'Panier'))
const favoritesLabel = computed(() =>
  favoriteCount.value > 0 ? `Favoris (${favoriteCount.value})` : 'Favoris',
)

const headerSearch = ref(parseSearchQuery(route.query.q))
const menuOpen = ref(false)
const scrolled = ref(false)

watch(
  () => [route.path, route.query.q],
  () => {
    menuOpen.value = false

    if (route.path === '/') {
      headerSearch.value = parseSearchQuery(route.query.q)
    }
  },
)

function onScroll() {
  scrolled.value = window.scrollY > 8
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
})

async function onHeaderSearch() {
  const q = headerSearch.value.trim()
  const query: Record<string, string> = {}

  if (route.path === '/') {
    for (const [key, value] of Object.entries(route.query)) {
      if (key === 'page' || key === 'q' || typeof value !== 'string') {
        continue
      }

      query[key] = value
    }
  }

  if (q !== '') {
    query.q = q
  }

  menuOpen.value = false
  await router.push({ path: '/', query })
}

async function onLogout() {
  if (!repository || !session) {
    return
  }

  menuOpen.value = false
  await logout(repository, session)
  await router.push('/')
}
</script>

<template>
  <header
    class="sticky top-0 z-50 border-b transition-colors duration-300"
    :class="scrolled ? 'border-line bg-canvas/85 backdrop-blur-xl' : 'border-transparent bg-canvas'"
  >
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-5 py-3.5 lg:px-6">
      <RouterLink to="/" class="group flex items-center gap-2.5" aria-label="Shoppy">
        <span class="text-strong transition-transform duration-300 group-hover:-rotate-6">
          <BrandMark :size="34" />
        </span>
        <span class="display-tight hidden text-2xl text-strong sm:block">Shoppy</span>
      </RouterLink>

      <nav class="ml-6 hidden items-center gap-7 lg:flex" aria-label="Navigation principale">
        <RouterLink to="/" class="nav-link">Catalogue</RouterLink>
        <template v-if="isAuthenticated">
          <RouterLink to="/orders" class="nav-link">Commandes</RouterLink>
          <RouterLink v-if="isAdmin" to="/admin" class="nav-link">Admin</RouterLink>
        </template>
      </nav>

      <div class="ml-auto hidden min-w-0 max-w-sm flex-1 lg:block">
        <AppSearchForm
          id="header-search"
          v-model="headerSearch"
          label="Rechercher dans le catalogue"
          placeholder="Rechercher..."
          @submit="onHeaderSearch"
        />
      </div>

      <div class="ml-auto flex items-center gap-1.5 lg:ml-3">
        <span class="hidden lg:block">
          <ThemeToggle />
        </span>

        <RouterLink
          to="/favorites"
          class="btn-icon relative hidden sm:inline-flex"
          :aria-label="favoritesLabel"
        >
          <AppIcon name="heart" :size="19" />
          <span
            v-if="favoriteCount > 0"
            class="numeric absolute top-1 right-1 flex min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[0.6rem] font-bold text-accent-fg"
            >{{ favoriteCount }}</span
          >
        </RouterLink>

        <RouterLink to="/cart" class="btn-icon relative" :aria-label="cartLabel">
          <AppIcon name="cart" :size="19" />
          <span
            v-if="itemCount > 0"
            class="numeric absolute top-1 right-1 flex min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[0.6rem] font-bold text-accent-fg"
            >{{ itemCount }}</span
          >
        </RouterLink>

        <template v-if="isAuthenticated">
          <span class="ml-1 hidden md:block">
            <AccountMenu
              :email="email"
              :role-label="roleLabel"
              :is-admin="isAdmin"
              :favorite-count="favoriteCount"
              :cart-count="itemCount"
              @logout="onLogout"
            />
          </span>
        </template>
        <template v-else>
          <RouterLink to="/login" class="btn-ghost hidden md:inline-flex">Connexion</RouterLink>
          <RouterLink to="/register" class="btn-primary hidden md:inline-flex">Inscription</RouterLink>
        </template>

        <button
          type="button"
          class="btn-icon lg:hidden"
          :aria-label="menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
          :aria-expanded="menuOpen"
          @click="menuOpen = !menuOpen"
        >
          <AppIcon :name="menuOpen ? 'close' : 'menu'" :size="20" />
        </button>
      </div>
    </div>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      leave-active-class="transition duration-150 ease-in"
      enter-from-class="opacity-0 -translate-y-2"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <AppMobileNav
        v-if="menuOpen"
        v-model:search="headerSearch"
        :is-authenticated="isAuthenticated"
        :is-admin="isAdmin"
        @search="onHeaderSearch"
        @logout="onLogout"
      />
    </Transition>
  </header>
</template>
