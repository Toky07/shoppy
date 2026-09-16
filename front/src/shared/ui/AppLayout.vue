<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { logout } from '@/modules/auth/application/logout'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { useFavorites } from '@/modules/catalog/application/useFavorites'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { userRoleLabel } from '@/modules/auth/ui/userRoleLabel'
import AccountMenu from './AccountMenu.vue'
import AppIcon from './AppIcon.vue'
import BackToTop from './BackToTop.vue'
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
const year = new Date().getFullYear()

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
  <div class="flex min-h-screen flex-col bg-canvas">
    <a
      href="#main"
      class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-60 focus:rounded-full focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:text-primary-fg"
    >
      Aller au contenu
    </a>

    <!-- Bandeau de réassurance -->
    <div class="hidden bg-primary text-primary-fg sm:block dark:bg-canvas-deep dark:text-muted">
      <div
        class="mx-auto flex max-w-7xl items-center justify-center gap-8 px-6 py-2 text-[0.7rem] font-medium tracking-[0.08em] uppercase"
      >
        <span class="inline-flex items-center gap-2">
          <AppIcon name="truck" :size="14" />
          Livraison offerte dès 49 €
        </span>
        <span class="hidden items-center gap-2 md:inline-flex">
          <AppIcon name="refresh" :size="14" />
          Retours sous 30 jours
        </span>
        <span class="hidden items-center gap-2 lg:inline-flex">
          <AppIcon name="shield" :size="14" />
          Paiement sécurisé par Stripe
        </span>
      </div>
    </div>

    <header
      class="sticky top-0 z-50 border-b transition-colors duration-300"
      :class="
        scrolled
          ? 'border-line bg-canvas/85 backdrop-blur-xl'
          : 'border-transparent bg-canvas'
      "
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

        <form
          class="ml-auto hidden min-w-0 max-w-sm flex-1 lg:block"
          role="search"
          @submit.prevent="onHeaderSearch"
        >
          <label class="sr-only" for="header-search">Rechercher dans le catalogue</label>
          <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
              <AppIcon name="search" :size="16" />
            </span>
            <input
              id="header-search"
              v-model="headerSearch"
              type="search"
              placeholder="Rechercher..."
              class="field rounded-full bg-surface-inset py-2.5 pl-11 text-sm"
            />
          </div>
        </form>

        <div class="ml-auto flex items-center gap-1.5 lg:ml-3">
          <span class="hidden lg:block">
            <ThemeToggle />
          </span>

          <RouterLink to="/favorites" class="btn-icon relative hidden sm:inline-flex" :aria-label="favoritesLabel">
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

      <!-- Menu mobile -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        leave-active-class="transition duration-150 ease-in"
        enter-from-class="opacity-0 -translate-y-2"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="menuOpen" class="border-t border-line bg-surface lg:hidden">
          <div class="mx-auto max-w-7xl space-y-5 px-5 py-5">
            <form role="search" @submit.prevent="onHeaderSearch">
              <label class="sr-only" for="mobile-search">Rechercher un article</label>
              <div class="relative">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
                  <AppIcon name="search" :size="16" />
                </span>
                <input
                  id="mobile-search"
                  v-model="headerSearch"
                  type="search"
                  placeholder="Rechercher un article..."
                  class="field rounded-full bg-surface-inset py-2.5 pl-11"
                />
              </div>
            </form>

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
                <button type="button" class="btn-outline btn-sm" @click="onLogout">
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
      </Transition>
    </header>

    <main id="main" class="mx-auto w-full max-w-7xl flex-1 px-5 py-10 lg:px-6 lg:py-14">
      <slot />
    </main>

    <footer class="mt-10 border-t border-line bg-surface">
      <div class="mx-auto max-w-7xl px-5 py-14 lg:px-6">
        <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
          <div>
            <div class="flex items-center gap-2.5">
              <span class="text-strong"><BrandMark :size="30" /></span>
              <span class="display-tight text-xl text-strong">Shoppy</span>
            </div>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">
              Une sélection courte d'objets bien faits. Expédiés vite, payés en toute sécurité.
            </p>
            <form class="mt-6 max-w-xs" @submit.prevent>
              <label class="field-label" for="newsletter">Lettre d'information</label>
              <div class="flex gap-2">
                <input
                  id="newsletter"
                  type="email"
                  placeholder="vous@domaine.com"
                  class="field rounded-full py-2.5 text-sm"
                />
                <button type="submit" class="btn-accent shrink-0" aria-label="S'abonner">
                  <AppIcon name="arrow-right" :size="17" />
                </button>
              </div>
            </form>
          </div>

          <div>
            <p class="field-label">Boutique</p>
            <ul class="space-y-3 text-sm">
              <li><RouterLink to="/" class="link-quiet">Tous les produits</RouterLink></li>
              <li><RouterLink to="/favorites" class="link-quiet">Ma liste d'envies</RouterLink></li>
              <li><RouterLink to="/cart" class="link-quiet">Mon panier</RouterLink></li>
              <li><RouterLink to="/orders" class="link-quiet">Mes achats</RouterLink></li>
            </ul>
          </div>

          <div>
            <p class="field-label">Aide</p>
            <ul class="space-y-3 text-sm">
              <li><span class="link-quiet">Livraison &amp; retours</span></li>
              <li><span class="link-quiet">Paiement sécurisé</span></li>
              <li><span class="link-quiet">Nous écrire</span></li>
              <li><span class="link-quiet">Questions fréquentes</span></li>
            </ul>
          </div>

          <div>
            <p class="field-label">Suivez-nous</p>
            <ul class="space-y-3 text-sm">
              <li><span class="link-quiet">Instagram</span></li>
              <li><span class="link-quiet">LinkedIn</span></li>
              <li><span class="link-quiet">Journal</span></li>
            </ul>
          </div>
        </div>

        <div
          class="mt-12 flex flex-col-reverse items-center justify-between gap-6 border-t border-line pt-8 sm:flex-row"
        >
          <p class="text-xs text-faint">© {{ year }} Shoppy · Conçu et développé avec soin.</p>
          <div class="flex items-center gap-2 text-faint">
            <span class="badge-neutral"><AppIcon name="credit-card" :size="13" /> Visa</span>
            <span class="badge-neutral">Mastercard</span>
            <span class="badge-neutral"><AppIcon name="lock" :size="13" /> Stripe</span>
          </div>
        </div>
      </div>
    </footer>

    <BackToTop />
  </div>
</template>
