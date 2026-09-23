<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import AuthRequiredPanel from '@/modules/auth/ui/AuthRequiredPanel.vue'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { paymentRepositoryKey } from '@/modules/payment/application/paymentRepositoryKey'
import { usePaymentByOrder } from '@/modules/payment/application/usePaymentByOrder'
import { orderRepositoryKey } from '../application/orderRepositoryKey'
import { useOrder } from '../application/useOrder'
import OrderCheckoutPanel from './OrderCheckoutPanel.vue'
import OrderAddresses from './OrderAddresses.vue'
import OrderDetailHeader from './OrderDetailHeader.vue'
import OrderLine from './OrderLine.vue'
import { orderErrorMessage } from './orderErrorMessage'

const session = inject(authSessionKey)
const orderRepository = inject(orderRepositoryKey)
const paymentRepository = inject(paymentRepositoryKey)

if (!session || !orderRepository || !paymentRepository) {
  throw new Error('Order dependencies are not provided.')
}

const authSession = session
const orders = orderRepository
const payments = paymentRepository
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const route = useRoute()
const orderId = computed(() => String(route.params.id ?? ''))
const { status, order, error, reload } = useOrder(orders, orderId, isAuthenticated)
const { payment, reload: reloadPayment } = usePaymentByOrder(payments, orderId, isAuthenticated)
const { pending, errorMessage: actionError, run } = usePendingAction((error) =>
  orderErrorMessage(error),
)
const notFound = computed(
  () => error.value?.code === 'order_not_found' || error.value?.code === 'forbidden',
)
const loadError = computed(() => {
  if (!error.value) {
    return undefined
  }
  return notFound.value ? 'Cette commande est introuvable.' : orderErrorMessage(error.value)
})
const canPay = computed(
  () => order.value?.status === 'pending' && payment.value?.status === 'pending',
)
const canCancel = computed(() => order.value?.status === 'pending')

function onPay() {
  return run(async () => {
    const checkout = await payments.startCheckout({
      orderId: orderId.value,
      successUrl: `${window.location.origin}/orders/${orderId.value}?payment=success`,
      cancelUrl: `${window.location.origin}/orders/${orderId.value}?payment=cancel`,
    })

    if (checkout.redirectUrl) {
      window.location.assign(checkout.redirectUrl)
      return
    }

    await reload()
    await reloadPayment()
  })
}

function onCancel() {
  return run(async () => {
    await orders.cancel(orderId.value)
    await reload()
    await reloadPayment()
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <nav class="mb-8 text-sm" aria-label="Fil d'Ariane">
      <RouterLink to="/orders" class="inline-flex items-center gap-2 font-medium link-quiet">
        <AppIcon name="arrow-left" :size="15" />
        Retour aux commandes
      </RouterLink>
    </nav>

    <AuthRequiredPanel
      v-if="!isAuthenticated"
      message="Vous devez être connecté pour voir les détails de cette commande."
      :redirect="route.path"
    />

    <PageStatus
      v-else
      :status="status === 'ready' ? 'ready' : status"
      :error-message="loadError"
      skeleton="rows"
    >
      <article v-if="order" class="mx-auto max-w-4xl">
        <div class="panel overflow-hidden">
          <OrderDetailHeader :order="order" :payment="payment" />
          <OrderAddresses :shipping="order.shippingAddress" :billing="order.billingAddress" />

          <div class="p-6 sm:p-8">
            <h2 class="field-label">Articles commandés</h2>
            <ul class="divide-y divide-line">
              <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
            </ul>
          </div>

          <OrderCheckoutPanel
            :total="order.total"
            :shipping="order.shipping"
            :pending="pending"
            :can-pay="canPay"
            :can-cancel="canCancel"
            :action-error="actionError"
            @pay="onPay"
            @cancel="onCancel"
          />
        </div>

        <p class="mt-6 flex items-center justify-center gap-2 text-xs text-faint">
          <AppIcon name="shield" :size="14" />
          Paiement traité par Stripe · votre commande est confirmée dès validation bancaire
        </p>
      </article>
    </PageStatus>
  </section>
</template>
