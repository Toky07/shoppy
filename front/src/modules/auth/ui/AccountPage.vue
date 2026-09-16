<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import ThemeToggle from '@/shared/ui/ThemeToggle.vue'
import type { IconName } from '@/shared/ui/icons'
import { formatDate } from '@/shared/datetime/formatDate'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { useFavorites } from '@/modules/catalog/application/useFavorites'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { logout } from '../application/logout'
import { userRoleLabel } from './userRoleLabel'

const session = inject(authSessionKey)
const repository = inject(authRepositoryKey)
const cartState = inject(cartStateKey)
const router = useRouter()
const { count: favoriteCount, clear: clearFavorites } = useFavorites()

const user = computed(() => session?.session.value?.user ?? null)
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const initials = computed(() => (user.value?.email ?? '').slice(0, 2).toUpperCase())
const cartCount = computed(() => cartState?.itemCount.value ?? 0)
const copied = ref(false)
const pending = ref(false)

const shortcuts = computed<{ to: string; label: string; icon: IconName; detail: string }[]>(() => [
  {
    to: '/orders',
    label: 'Mes commandes',
    icon: 'package',
    detail: 'Suivi, statuts et justificatifs',
  },
  {
    to: '/favorites',
    label: "Ma liste d'envies",
    icon: 'heart',
    detail:
      favoriteCount.value > 0
        ? `${favoriteCount.value} produit${favoriteCount.value > 1 ? 's' : ''} gardé${favoriteCount.value > 1 ? 's' : ''}`
        : 'Aucun produit pour le moment',
  },
  {
    to: '/cart',
    label: 'Mon panier',
    icon: 'cart',
    detail:
      cartCount.value > 0
        ? `${cartCount.value} article${cartCount.value > 1 ? 's' : ''} en attente`
        : 'Votre panier est vide',
  },
])

async function onCopyId() {
  if (!user.value) {
    return
  }

  try {
    await navigator.clipboard.writeText(user.value.id)
    copied.value = true
    window.setTimeout(() => (copied.value = false), 2000)
  } catch {
    /* presse-papiers indisponible */
  }
}

async function onLogout() {
  if (!repository || !session) {
    return
  }

  pending.value = true
  await logout(repository, session)
  pending.value = false
  await router.push('/')
}
</script>

<template>
  <section class="animate-fade-in">
    <div>
      <span class="badge-neutral"><AppIcon name="user" :size="13" /> Compte</span>
      <h1 class="display-tight mt-5 text-4xl text-strong sm:text-5xl">Mon profil</h1>
      <p class="mt-3 text-sm text-muted">
        Vos informations, vos raccourcis et vos préférences d'affichage.
      </p>
    </div>

    <div v-if="!isAuthenticated" class="panel mx-auto mt-12 max-w-md p-8 text-center">
      <div
        class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
      >
        <AppIcon name="lock" :size="24" />
      </div>
      <h2 class="text-xl font-bold text-strong">Connexion requise</h2>
      <p class="mt-3 text-sm text-muted">Connectez-vous pour consulter votre profil.</p>
      <RouterLink
        :to="{ path: '/login', query: { redirect: '/account' } }"
        class="btn-primary btn-lg mt-8 w-full"
      >
        Se connecter
      </RouterLink>
    </div>

    <div v-else-if="user" class="mt-10 grid items-start gap-6 lg:grid-cols-[1.15fr_1fr]">
      <!-- Identité -->
      <div class="panel overflow-hidden">
        <div class="mesh grain flex items-center gap-4 p-6 sm:p-7">
          <span
            class="relative z-1 flex size-16 shrink-0 items-center justify-center rounded-2xl bg-primary font-display text-xl font-extrabold text-primary-fg shadow-lifted"
            aria-hidden="true"
            >{{ initials }}</span
          >
          <span class="relative z-1 min-w-0">
            <span class="block truncate font-display text-lg font-bold text-strong">
              {{ user.email }}
            </span>
            <span class="mt-2 flex flex-wrap items-center gap-2">
              <span :class="user.role === 'admin' ? 'badge-accent' : 'badge-neutral'">
                <AppIcon :name="user.role === 'admin' ? 'shield' : 'user'" :size="12" />
                {{ userRoleLabel(user.role) }}
              </span>
            </span>
          </span>
        </div>

        <dl class="divide-y divide-line">
          <div class="flex items-center justify-between gap-4 px-6 py-4 sm:px-7">
            <dt class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">
              Membre depuis
            </dt>
            <dd class="text-sm font-medium text-strong">{{ formatDate(user.createdAt) }}</dd>
          </div>
          <div class="flex items-center justify-between gap-4 px-6 py-4 sm:px-7">
            <dt class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">
              Identifiant
            </dt>
            <dd class="flex min-w-0 items-center gap-2">
              <span class="numeric truncate text-xs text-faint">{{ user.id }}</span>
              <button
                type="button"
                class="btn-icon size-8 shrink-0"
                :aria-label="copied ? 'Identifiant copié' : 'Copier mon identifiant'"
                :title="copied ? 'Identifiant copié' : 'Copier mon identifiant'"
                @click="onCopyId"
              >
                <AppIcon :name="copied ? 'check' : 'copy'" :size="15" />
              </button>
            </dd>
          </div>
        </dl>

        <div class="border-t border-line bg-surface-inset px-6 py-5 sm:px-7">
          <p class="flex items-center gap-2 text-xs text-muted">
            <AppIcon name="shield" :size="15" />
            Session active sur cet appareil uniquement.
          </p>
          <button type="button" class="btn-danger mt-4" :disabled="pending" @click="onLogout">
            <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="15" /></span>
            <AppIcon v-else name="logout" :size="15" />
            Se déconnecter
          </button>
        </div>
      </div>

      <div class="grid gap-6">
        <!-- Raccourcis -->
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
              v-if="user.role === 'admin'"
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

        <!-- Préférences -->
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
      </div>
    </div>
  </section>
</template>
