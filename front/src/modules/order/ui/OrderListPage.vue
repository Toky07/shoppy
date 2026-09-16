<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { formatDate } from '@/shared/datetime/formatDate'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { orderRepositoryKey } from '../application/orderRepositoryKey'
import { DEFAULT_ORDER_LIMIT } from '../application/defaultOrderLimit'
import { useOrderList } from '../application/useOrderList'
import { orderErrorMessage } from './orderErrorMessage'
import { orderStatusLabel } from './orderStatusLabel'
import { orderStatusStyle } from './orderStatusStyle'

const session = inject(authSessionKey)
const repository = inject(orderRepositoryKey)

if (!session || !repository) {
  throw new Error('Order dependencies are not provided.')
}

const authSession = session
const orderRepository = repository
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const route = useRoute()
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_ORDER_LIMIT,
}))
const { status, page, error } = useOrderList(orderRepository, query, isAuthenticated)
const loadError = computed(() => (error.value ? orderErrorMessage(error.value) : undefined))

function itemCount(count: number) {
  return `${count} article${count > 1 ? 's' : ''}`
}
</script>

<template>
  <section class="animate-fade-in">
    <div>
      <span class="badge-neutral"><AppIcon name="package" :size="13" /> Historique</span>
      <h1 class="display-tight mt-5 text-4xl text-strong sm:text-5xl">Mes Commandes</h1>
      <p class="mt-3 text-sm text-muted">
        Suivez l'état de vos achats et retrouvez vos justificatifs.
      </p>
    </div>

    <template v-if="!isAuthenticated">
      <div class="panel mx-auto mt-12 max-w-md p-8 text-center">
        <div
          class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
        >
          <AppIcon name="lock" :size="24" />
        </div>
        <h2 class="text-xl font-bold text-strong">Connexion requise</h2>
        <p class="mt-3 text-sm text-muted">
          Vous devez être connecté pour voir l'historique de vos commandes.
        </p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: '/orders' } }"
          class="btn-primary btn-lg mt-8 w-full"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>

    <div v-else class="mt-10">
      <PageStatus :status="status" :error-message="loadError" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="package"
            title="Aucune commande"
            description="Vous n'avez pas encore passé de commande. Le catalogue vous attend."
          >
            <RouterLink to="/" class="btn-primary btn-lg">
              Explorer le catalogue
              <AppIcon name="arrow-right" :size="16" />
            </RouterLink>
          </EmptyState>
        </template>

        <div v-if="page" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article
            v-for="order in page.items"
            :key="order.id"
            class="group panel relative flex flex-col p-6 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lifted"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-semibold tracking-[0.12em] text-faint uppercase">
                  N° {{ order.id.slice(0, 8).toUpperCase() }}
                </p>
                <p class="mt-2 flex items-center gap-2 text-sm font-medium text-body">
                  <AppIcon name="calendar" :size="14" />
                  {{ formatDate(order.createdAt) }}
                </p>
              </div>
              <span :class="orderStatusStyle(order.status).badge">
                <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
                {{ orderStatusLabel(order.status) }}
              </span>
            </div>

            <div class="mt-6 flex items-end justify-between gap-4">
              <div>
                <p class="text-xs text-muted">{{ itemCount(order.items.length) }}</p>
                <p class="numeric mt-1 font-display text-2xl font-extrabold text-strong">
                  <ProductPrice :price="order.total" />
                </p>
              </div>
            </div>

            <div class="mt-6 border-t border-line pt-4">
              <RouterLink
                :to="{ name: 'order', params: { id: order.id } }"
                class="inline-flex items-center gap-2 text-sm font-semibold text-strong"
              >
                Voir les détails
                <AppIcon
                  name="arrow-right"
                  :size="15"
                  class="transition-transform duration-300 group-hover:translate-x-1"
                />
              </RouterLink>
            </div>
          </article>
        </div>

        <div v-if="page" class="mt-12 flex justify-center">
          <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
