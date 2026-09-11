<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
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
import AdminGate from './AdminGate.vue'

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
  <section>
    <p class="mb-6 text-sm">
      <RouterLink to="/admin" class="text-stone-600 hover:text-stone-900">Retour à l'administration</RouterLink>
    </p>
    <h1 class="text-2xl font-semibold tracking-tight">Commandes</h1>
    <AdminGate redirect="/admin/orders">
      <div class="mt-6">
        <PageStatus :status="status" :error-message="loadError">
          <template #empty>Aucune commande pour le moment.</template>
          <ul v-if="page" class="space-y-3">
            <li
              v-for="order in page.items"
              :key="order.id"
              class="rounded-xl border border-stone-200 bg-white p-4"
            >
              <p class="font-semibold">{{ formatDate(order.createdAt) }}</p>
              <p class="mt-1 text-sm text-stone-600">
                {{ orderStatusLabel(order.status) }}
                ·
                <ProductPrice :price="order.total" />
              </p>
              <p class="mt-1 break-all text-xs text-stone-500">Client {{ order.customerId }}</p>
              <p class="mt-3">
                <RouterLink
                  :to="{ name: 'admin-order', params: { id: order.id } }"
                  class="text-sm text-stone-900 underline"
                >
                  Voir la commande du {{ formatDate(order.createdAt) }}
                </RouterLink>
              </p>
            </li>
          </ul>
          <Pagination
            v-if="page"
            :page="page.page"
            :limit="page.limit"
            :total="page.total"
          />
        </PageStatus>
      </div>
    </AdminGate>
  </section>
</template>
