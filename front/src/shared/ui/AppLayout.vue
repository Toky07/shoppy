<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { logout } from '@/modules/auth/application/logout'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'

const session = inject(authSessionKey)
const repository = inject(authRepositoryKey)
const cartState = inject(cartStateKey)
const router = useRouter()
const route = useRoute()
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const isAdmin = computed(() => session?.isAdmin.value ?? false)
const email = computed(() => session?.session.value?.user.email ?? '')
const itemCount = computed(() => cartState?.itemCount.value ?? 0)
const cartLabel = computed(() => (itemCount.value > 0 ? `Panier (${itemCount.value})` : 'Panier'))
const headerSearch = ref(parseSearchQuery(route.query.q))

watch(
  () => [route.path, route.query.q],
  () => {
    if (route.path === '/') {
      headerSearch.value = parseSearchQuery(route.query.q)
    }
  },
)

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

  await router.push({ path: '/', query })
}

async function onLogout() {
  if (!repository || !session) {
    return
  }

  await logout(repository, session)
  await router.push('/')
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50 text-gray-800 font-sans">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-2 group">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg group-hover:shadow-indigo-500/30 transition-all duration-300">
            <i class="fa-solid fa-store text-lg"></i>
          </div>
          <span class="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">Shoppy</span>
        </RouterLink>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center gap-8 font-medium text-gray-600">
          <RouterLink to="/" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-compass"></i> Catalogue
          </RouterLink>
          <template v-if="isAuthenticated">
            <RouterLink to="/orders" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
              <i class="fa-solid fa-box-open"></i> Commandes
            </RouterLink>
            <RouterLink v-if="isAdmin" to="/admin" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
              <i class="fa-solid fa-shield-halved"></i> Admin
            </RouterLink>
          </template>
        </nav>

        <form
          class="hidden min-w-0 max-w-md flex-1 lg:block"
          role="search"
          @submit.prevent="onHeaderSearch"
        >
          <label class="sr-only" for="header-search">Rechercher dans le catalogue</label>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
            <input
              id="header-search"
              v-model="headerSearch"
              type="search"
              placeholder="Rechercher..."
              class="w-full rounded-full border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"
            />
          </div>
        </form>

        <!-- Actions -->
        <div class="flex items-center gap-4">
          <RouterLink
            to="/cart"
            class="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors"
            :aria-label="cartLabel"
          >
            <i class="fa-solid fa-cart-shopping text-xl"></i>
            <span v-if="itemCount > 0" class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white shadow-sm">
              {{ itemCount }}
            </span>
          </RouterLink>

          <div class="h-6 w-px bg-gray-200 mx-2"></div>

          <template v-if="isAuthenticated">
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-gray-100 px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-user-circle text-gray-500"></i>
                {{ email }}
              </div>
              <button
                type="button"
                class="text-gray-500 hover:text-red-500 transition-colors p-2"
                aria-label="Déconnexion"
                title="Déconnexion"
                @click="onLogout"
              >
                <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
              </button>
            </div>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">Connexion</RouterLink>
            <RouterLink to="/register" class="text-sm font-medium px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all">Inscription</RouterLink>
          </template>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow mx-auto w-full max-w-7xl px-6 py-10">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12 mt-auto">
      <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-store text-indigo-600 text-xl"></i>
          <span class="text-xl font-bold text-gray-900">Shoppy</span>
        </div>
        <p class="text-gray-500 text-sm">© 2026 Shoppy. Tous droits réservés.</p>
        <div class="flex gap-4 text-gray-400">
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="fa-brands fa-twitter text-xl"></i></a>
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="fa-brands fa-instagram text-xl"></i></a>
          <a href="#" class="hover:text-indigo-600 transition-colors"><i class="fa-brands fa-facebook text-xl"></i></a>
        </div>
      </div>
    </footer>
  </div>
</template>
