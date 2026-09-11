<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { orderRepositoryKey } from '@/modules/order/application/orderRepositoryKey'
import { useOrder } from '@/modules/order/application/useOrder'
import OrderLine from '@/modules/order/ui/OrderLine.vue'
import { orderErrorMessage } from '@/modules/order/ui/orderErrorMessage'
import { orderStatusLabel } from '@/modules/order/ui/orderStatusLabel'
import AdminGate from './AdminGate.vue'

const session = inject(authSessionKey)
const orderRepository = inject(orderRepositoryKey)

if (!session || !orderRepository) {
  throw new Error('Admin order dependencies are not provided.')
}

const orders = orderRepository
const isAdmin = computed(() => session.isAdmin.value)
const route = useRoute()
const orderId = computed(() => String(route.params.id ?? ''))
const { status, order, error, reload } = useOrder(orders, orderId, isAdmin)
const pending = ref(false)
const actionError = ref<string>()
const loadError = computed(() => (error.value ? orderErrorMessage(error.value) : undefined))
const canMarkPaid = computed(() => order.value?.status === 'pending')

async function onMarkPaid() {
  pending.value = true
  actionError.value = undefined
  try {
    await orders.markPaid(orderId.value)
    await reload()
  } catch (caught) {
    actionError.value = orderErrorMessage(toApiError(caught))
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <section>
    <p class="mb-6 text-sm">
      <RouterLink to="/admin/orders" class="text-stone-600 hover:text-stone-900">Retour aux commandes</RouterLink>
    </p>
    <h1 class="text-2xl font-semibold tracking-tight">Commande</h1>
    <AdminGate :redirect="route.path">
      <div class="mt-6">
        <PageStatus :status="status === 'ready' ? 'ready' : status" :error-message="loadError">
        <article v-if="order" class="mt-6">
          <h2 class="text-xl font-semibold">Commande du {{ formatDate(order.createdAt) }}</h2>
          <p class="mt-2 text-stone-600">{{ orderStatusLabel(order.status) }}</p>
          <p class="mt-1 break-all text-sm text-stone-500">Client {{ order.customerId }}</p>
          <ul class="mt-6 divide-y divide-stone-200 border-y border-stone-200">
            <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
          </ul>
          <p class="mt-4 text-lg font-semibold">
            Total
            <ProductPrice :price="order.total" />
          </p>
          <p v-if="actionError" class="mt-4" role="alert">{{ actionError }}</p>
          <button
            v-if="canMarkPaid"
            type="button"
            class="mt-6 rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800 disabled:opacity-50"
            :disabled="pending"
            @click="onMarkPaid"
          >
            Marquer comme payée
          </button>
        </article>
      </PageStatus>
      </div>
    </AdminGate>
  </section>
</template>
