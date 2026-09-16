<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { paymentRepositoryKey } from '@/modules/payment/application/paymentRepositoryKey'
import { usePaymentByOrder } from '@/modules/payment/application/usePaymentByOrder'
import { paymentStatusLabel } from '@/modules/payment/ui/paymentStatusLabel'
import { orderRepositoryKey } from '../application/orderRepositoryKey'
import { useOrder } from '../application/useOrder'
import OrderLine from './OrderLine.vue'
import { orderErrorMessage } from './orderErrorMessage'
import { orderStatusLabel } from './orderStatusLabel'
import { orderStatusStyle } from './orderStatusStyle'

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
const pending = ref(false)
const actionError = ref<string>()
const notFound = computed(
  () => error.value?.code === 'order_not_found' || error.value?.code === 'forbidden',
)
const loadError = computed(() => {
  if (!error.value) {
    return undefined
  }
  return notFound.value ? 'Cette commande est introuvable.' : orderErrorMessage(error.value)
})
const canPay = computed(() => order.value?.status === 'pending' && payment.value?.status === 'pending')
const canCancel = computed(() => order.value?.status === 'pending')

async function run(action: () => Promise<unknown>) {
  pending.value = true
  actionError.value = undefined

  try {
    await action()
  } catch (caught) {
    actionError.value = orderErrorMessage(toApiError(caught))
  } finally {
    pending.value = false
  }
}

function onPay() {
  return run(async () => {
    const checkout = await payments.startCheckout({
      orderId: orderId.value,
      provider: 'stripe',
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

    <template v-if="!isAuthenticated">
      <div class="panel mx-auto mt-12 max-w-md p-8 text-center">
        <div
          class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
        >
          <AppIcon name="lock" :size="24" />
        </div>
        <h2 class="text-xl font-bold text-strong">Connexion requise</h2>
        <p class="mt-3 text-sm text-muted">
          Vous devez être connecté pour voir les détails de cette commande.
        </p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: route.path } }"
          class="btn-primary btn-lg mt-8 w-full"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>

    <PageStatus
      v-else
      :status="status === 'ready' ? 'ready' : status"
      :error-message="loadError"
      skeleton="rows"
    >
      <article v-if="order" class="mx-auto max-w-4xl">
        <div class="panel overflow-hidden">
          <!-- En-tête -->
          <div
            class="mesh grain flex flex-col gap-5 border-b border-line p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8"
          >
            <div class="relative z-1">
              <h1 class="display-tight text-3xl text-strong">
                Commande <span class="numeric text-accent-strong">#{{ order.id.slice(0, 8).toUpperCase() }}</span>
              </h1>
              <p class="mt-3 flex items-center gap-2 text-sm text-muted">
                <AppIcon name="calendar" :size="15" />
                Passée le {{ formatDate(order.createdAt) }}
              </p>
            </div>
            <div class="relative z-1 flex flex-col items-start gap-2 sm:items-end">
              <span :class="orderStatusStyle(order.status).badge">
                <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
                {{ orderStatusLabel(order.status) }}
              </span>
              <span v-if="payment" class="flex items-center gap-1.5 text-xs font-medium text-muted"
                ><AppIcon name="credit-card" :size="14" />Paiement : {{ paymentStatusLabel(payment.status) }}</span
              >
            </div>
          </div>

          <!-- Articles -->
          <div class="p-6 sm:p-8">
            <h2 class="field-label">Articles commandés</h2>
            <ul class="divide-y divide-line">
              <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
            </ul>
          </div>

          <!-- Total et actions -->
          <div class="border-t border-line bg-surface-inset p-6 sm:p-8">
            <div v-if="actionError" role="alert" class="notice-danger mb-6">
              <AppIcon name="alert-circle" :size="18" class="mt-0.5" />
              <span>{{ actionError }}</span>
            </div>

            <dl class="ml-auto max-w-xs space-y-3 text-sm">
              <div class="flex items-baseline justify-between gap-6">
                <dt class="text-muted">Sous-total</dt>
                <dd class="numeric font-semibold text-strong">
                  <ProductPrice :price="order.total" />
                </dd>
              </div>
              <div class="flex items-baseline justify-between gap-6">
                <dt class="text-muted">Livraison</dt>
                <dd class="font-semibold text-positive">Offerte</dd>
              </div>
              <div
                class="flex items-baseline justify-between gap-6 border-t border-line pt-3 text-base"
              >
                <dt class="font-semibold text-strong">Total</dt>
                <dd class="numeric font-display text-2xl font-extrabold text-strong">
                  <ProductPrice :price="order.total" />
                </dd>
              </div>
            </dl>

            <div v-if="canPay || canCancel" class="mt-8 flex flex-wrap justify-end gap-3">
              <button
                v-if="canCancel"
                type="button"
                class="btn-outline"
                :disabled="pending"
                @click="onCancel"
              >
                <AppIcon name="close" :size="15" />
                Annuler la commande
              </button>
              <button
                v-if="canPay"
                type="button"
                class="btn-primary btn-lg"
                :disabled="pending"
                @click="onPay"
              >
                <span v-if="pending" class="animate-orbit">
                  <AppIcon name="loader" :size="17" />
                </span>
                <AppIcon v-else name="credit-card" :size="16" />
                Procéder au paiement
              </button>
            </div>
          </div>
        </div>

        <p class="mt-6 flex items-center justify-center gap-2 text-xs text-faint">
          <AppIcon name="shield" :size="14" />
          Paiement traité par Stripe · votre commande est confirmée dès validation bancaire
        </p>
      </article>
    </PageStatus>
  </section>
</template>
