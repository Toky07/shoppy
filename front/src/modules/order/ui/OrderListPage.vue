<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageHeader from '@/shared/ui/PageHeader.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import AuthRequiredPanel from '@/modules/auth/ui/AuthRequiredPanel.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { orderRepositoryKey } from '../application/orderRepositoryKey'
import { DEFAULT_ORDER_LIMIT } from '../application/defaultOrderLimit'
import { useOrderList } from '../application/useOrderList'
import OrderCard from './OrderCard.vue'
import { orderErrorMessage } from './orderErrorMessage'

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
</script>

<template>
  <section class="animate-fade-in">
    <PageHeader
      eyebrow="Historique"
      title="Mes Commandes"
      icon="package"
      description="Suivez l'état de vos achats et retrouvez vos justificatifs."
    />

    <AuthRequiredPanel
      v-if="!isAuthenticated"
      message="Vous devez être connecté pour voir l'historique de vos commandes."
      redirect="/orders"
    />

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
          <OrderCard v-for="order in page.items" :key="order.id" :order="order" />
        </div>

        <div v-if="page" class="mt-12 flex justify-center">
          <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
