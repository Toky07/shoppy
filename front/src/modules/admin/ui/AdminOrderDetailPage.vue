<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
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
import { orderStatusStyle } from '@/modules/order/ui/orderStatusStyle'
import AdminGate from './AdminGate.vue'
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
  <section class="animate-fade-in">
    <AdminPageHeader
      eyebrow="Console"
      title="Commande"
      icon="package"
      back-to="/admin/orders"
      back-label="Retour aux commandes"
    />

    <AdminGate :redirect="route.path">
      <div class="mt-10">
        <PageStatus
          :status="status === 'ready' ? 'ready' : status"
          :error-message="loadError"
          skeleton="rows"
        >
          <article v-if="order" class="panel mx-auto max-w-3xl overflow-hidden">
            <div
              class="flex flex-wrap items-center justify-between gap-4 border-b border-line bg-surface-inset p-6"
            >
              <div>
                <h2 class="font-display text-xl font-bold text-strong">
                  Commande du {{ formatDate(order.createdAt) }}
                </h2>
                <p class="numeric mt-2 text-xs break-all text-faint">Client {{ order.customerId }}</p>
              </div>
              <span :class="orderStatusStyle(order.status).badge">
                <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
                {{ orderStatusLabel(order.status) }}
              </span>
            </div>

            <div class="p-6">
              <h3 class="field-label">Articles</h3>
              <ul class="divide-y divide-line">
                <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
              </ul>
            </div>

            <div class="border-t border-line bg-surface-inset p-6">
              <div v-if="actionError" role="alert" class="notice-danger mb-5">
                <AppIcon name="alert-circle" :size="18" class="mt-0.5" />
                <span>{{ actionError }}</span>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-5">
                <div>
                  <p class="text-xs text-muted">Total</p>
                  <p class="numeric mt-1 font-display text-2xl font-extrabold text-strong">
                    <ProductPrice :price="order.total" />
                  </p>
                </div>

                <button
                  v-if="canMarkPaid"
                  type="button"
                  class="btn-primary"
                  :disabled="pending"
                  @click="onMarkPaid"
                >
                  <span v-if="pending" class="animate-orbit">
                    <AppIcon name="loader" :size="16" />
                  </span>
                  <AppIcon v-else name="check" :size="16" />
                  Marquer comme payée
                </button>
              </div>
            </div>
          </article>
        </PageStatus>
      </div>
    </AdminGate>
  </section>
</template>
