<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import type { IconName } from '@/shared/ui/icons'
import { formatMoney } from '@/shared/money/formatMoney'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { adminDashboardRepositoryKey } from '@/modules/admin/application/adminDashboardRepositoryKey'
import type { AdminDashboard } from '@/modules/admin/domain/AdminDashboard'
import { stockLabel } from '@/modules/catalog/ui/lib/stockLabel'

const session = inject(authSessionKey)
const dashboardRepository = inject(adminDashboardRepositoryKey)

if (!dashboardRepository) {
  throw new Error('Admin dashboard dependencies are not provided.')
}

const catalog = dashboardRepository
const email = computed(() => session?.session.value?.user.email ?? '')
const status = ref<'loading' | 'ready' | 'empty' | 'error'>('loading')
const dashboard = ref<AdminDashboard>()
const errorMessage = ref<string>()

async function load() {
  status.value = 'loading'
  errorMessage.value = undefined
  try {
    dashboard.value = await catalog.get()
    status.value = 'ready'
  } catch (caught) {
    dashboard.value = undefined
    errorMessage.value = toApiError(caught).message
    status.value = 'error'
  }
}

onMounted(load)

const sections: { to: string; label: string; icon: IconName; description: string; action: string }[] = [
  {
    to: '/admin/orders',
    label: 'Commandes',
    icon: 'package',
    description: 'Suivre les achats, confirmer les paiements et consulter le détail.',
    action: 'Ouvrir les commandes',
  },
  {
    to: '/admin/products',
    label: 'Catalogue',
    icon: 'tag',
    description: 'Créer, modifier et retirer des produits, ajuster les stocks.',
    action: 'Gérer le catalogue',
  },
  {
    to: '/admin/reviews',
    label: 'Avis',
    icon: 'star',
    description: 'Traiter les signalements et masquer un avis du catalogue.',
    action: 'Modérer les avis',
  },
  {
    to: '/admin/users',
    label: 'Utilisateurs',
    icon: 'users',
    description: 'Lister les comptes, rechercher un email et attribuer un rôle.',
    action: 'Gérer les accès',
  },
  {
    to: '/admin/audit',
    label: 'Journal',
    icon: 'list',
    description: 'Voir qui a changé un rôle, un prix, un stock ou un paiement.',
    action: 'Ouvrir le journal',
  },
]
</script>

<template>
  <section class="animate-fade-in">
    <div
      class="relative overflow-hidden rounded-2xl border border-line bg-surface px-6 py-8 sm:px-8"
    >
      <div class="pointer-events-none absolute inset-0 mesh opacity-70"></div>
      <div class="relative">
        <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">
          Vue d'ensemble
        </p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-strong">
          Tableau de bord
        </h1>
        <p class="mt-2 max-w-lg text-sm text-muted">
          Bienvenue{{ email ? `, ${email}` : '' }}. La console est séparée de la boutique : ici, vous
          pilotez le catalogue, les commandes et les accès.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
          <RouterLink to="/admin/products/new" class="btn-primary">
            <AppIcon name="plus" :size="16" />
            Nouveau produit
          </RouterLink>
          <RouterLink to="/" class="btn-outline">
            <AppIcon name="globe" :size="16" />
            Voir la boutique
          </RouterLink>
        </div>
      </div>
    </div>

    <div class="mt-8">
      <PageStatus :status="status" :error-message="errorMessage" skeleton="rows">
        <div v-if="dashboard" class="grid gap-4 md:grid-cols-3">
          <article class="panel p-5">
            <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">
              Chiffre d'affaires
            </p>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-strong">
              {{ formatMoney(dashboard.revenue) }}
            </p>
            <p class="mt-1 text-sm text-muted">Commandes payées</p>
          </article>
          <RouterLink
            to="/admin/orders"
            class="panel p-5 transition-colors hover:border-line-strong hover:bg-surface-muted"
          >
            <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">
              Commandes du jour
            </p>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-strong">
              {{ dashboard.today.orders }}
            </p>
            <p class="mt-1 text-sm text-muted">{{ formatMoney(dashboard.today.revenue) }} encaissés</p>
          </RouterLink>
          <article class="panel p-5">
            <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">Stock bas</p>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-strong">
              {{ dashboard.lowStock.length }}
            </p>
            <p class="mt-1 text-sm text-muted">Produits à réapprovisionner</p>
          </article>
        </div>

        <div v-if="dashboard" class="panel mt-4 p-5">
          <div class="flex items-center justify-between gap-3">
            <h2 class="font-display text-lg font-bold text-strong">Stock à surveiller</h2>
            <RouterLink to="/admin/products" class="text-sm font-semibold text-strong">
              Catalogue
            </RouterLink>
          </div>
          <ul v-if="dashboard.lowStock.length > 0" class="mt-4 divide-y divide-line">
            <li v-for="product in dashboard.lowStock" :key="product.id" class="py-3 first:pt-0 last:pb-0">
              <RouterLink
                :to="`/admin/products/${product.id}`"
                class="flex items-center justify-between gap-3 text-sm"
              >
                <span class="font-semibold text-strong">{{ product.name }}</span>
                <span class="text-muted">{{ stockLabel(product.stock) }}</span>
              </RouterLink>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-muted">Tous les produits ont un stock suffisant.</p>
        </div>
      </PageStatus>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <RouterLink
        v-for="section in sections"
        :key="section.to"
        :to="section.to"
        :aria-label="section.label"
        class="group panel relative flex flex-col p-5 transition-colors hover:border-line-strong hover:bg-surface-muted"
      >
        <span
          class="flex size-11 items-center justify-center rounded-xl border border-line bg-surface-inset text-accent-strong transition-colors group-hover:bg-accent-soft"
        >
          <AppIcon :name="section.icon" :size="20" />
        </span>
        <h2 class="mt-4 font-display text-lg font-bold text-strong">{{ section.label }}</h2>
        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-muted">{{ section.description }}</p>
        <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-strong">
          {{ section.action }}
          <AppIcon
            name="arrow-right"
            :size="15"
            class="transition-transform duration-200 group-hover:translate-x-1"
          />
        </span>
      </RouterLink>
    </div>
  </section>
</template>
