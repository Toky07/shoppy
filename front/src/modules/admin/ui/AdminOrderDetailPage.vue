<script setup lang="ts">
import { computed, inject } from 'vue'
import { useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { orderRepositoryKey } from '@/modules/order/application/orderRepositoryKey'
import { useOrder } from '@/modules/order/application/useOrder'
import OrderLine from '@/modules/order/ui/OrderLine.vue'
import { orderErrorMessage } from '@/modules/order/ui/orderErrorMessage'
import AdminOrderSummary from './AdminOrderSummary.vue'
import AdminPageHeader from './AdminPageHeader.vue'

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
const { pending, errorMessage: actionError, run } = usePendingAction((error) =>
  orderErrorMessage(error),
)
const loadError = computed(() => (error.value ? orderErrorMessage(error.value) : undefined))
const canMarkPaid = computed(() => order.value?.status === 'pending')

function onMarkPaid() {
  return run(async () => {
    await orders.markPaid(orderId.value)
    await reload()
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      title="Commande"
      icon="package"
      back-to="/admin/orders"
      back-label="Retour aux commandes"
    />

    <div class="mt-6">
      <PageStatus
        :status="status === 'ready' ? 'ready' : status"
        :error-message="loadError"
        skeleton="rows"
      >
        <article v-if="order" class="panel mx-auto max-w-3xl overflow-hidden">
          <AdminOrderSummary
            :order="order"
            :pending="pending"
            :can-mark-paid="canMarkPaid"
            :action-error="actionError"
            @mark-paid="onMarkPaid"
          >
            <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
          </AdminOrderSummary>
        </article>
      </PageStatus>
    </div>
  </section>
</template>
