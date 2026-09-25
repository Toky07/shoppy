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
import { orderRepositoryKey } from '@/modules/order/application/orderRepositoryKey'
import { DEFAULT_ORDER_LIMIT } from '@/modules/order/application/defaultOrderLimit'
import { useOrderList } from '@/modules/order/application/useOrderList'
import { orderErrorMessage } from '@/modules/order/ui/orderErrorMessage'
import { orderStatusLabel } from '@/modules/order/ui/orderStatusLabel'
import { orderStatusStyle } from '@/modules/order/ui/orderStatusStyle'
import AdminPageHeader from './AdminPageHeader.vue'

const session = inject(authSessionKey)
const repository = inject(orderRepositoryKey)

if (!session || !repository) {
  throw new Error('Admin order dependencies are not provided.')
}

const isAdmin = computed(() => session.isAdmin.value)
const route = useRoute()
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_ORDER_LIMIT,
}))
const { status, page, error } = useOrderList(
  repository,
  query,
  isAdmin,
  (orders, currentQuery) => orders.listAll(currentQuery),
)
const loadError = computed(() => (error.value ? orderErrorMessage(error.value) : undefined))
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      title="Commandes"
      icon="package"
      description="Toutes les commandes de la boutique, de la plus récente à la plus ancienne."
    />

    <div class="mt-6">
      <PageStatus :status="status" :error-message="loadError" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="package"
            title="Aucune commande"
            description="Les commandes apparaîtront ici dès le premier achat."
          />
        </template>

        <div v-if="page" class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Statut</th>
                <th class="text-right">Total</th>
                <th class="w-32"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in page.items" :key="order.id">
                <td class="font-semibold text-strong">{{ formatDate(order.createdAt) }}</td>
                <td class="numeric text-xs text-muted">{{ order.customerEmail }}</td>
                <td>
                  <span :class="orderStatusStyle(order.status).badge">
                    <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
                    {{ orderStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="numeric text-right font-semibold text-strong">
                  <ProductPrice :price="order.total" />
                </td>
                <td class="text-right">
                  <RouterLink
                    :to="{ name: 'admin-order', params: { id: order.id } }"
                    class="btn-outline btn-sm"
                  >
                    Voir <span class="sr-only">la commande du {{ formatDate(order.createdAt) }}</span>
                    <AppIcon name="arrow-right" :size="14" />
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="page" class="mt-8 flex justify-center">
          <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
