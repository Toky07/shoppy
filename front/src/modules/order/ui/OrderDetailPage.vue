<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
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
const notFound = computed(() => error.value?.code === 'order_not_found' || error.value?.code === 'forbidden')
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
    <nav class="mb-8 flex items-center text-sm font-medium text-gray-500">
      <RouterLink to="/orders" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Retour aux commandes
      </RouterLink>
    </nav>
    
    <template v-if="!isAuthenticated">
      <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center max-w-md mx-auto mt-12">
        <div class="h-20 w-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-600">
          <i class="fa-solid fa-lock text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Connexion requise</h2>
        <p class="text-gray-500 mb-8">Vous devez être connecté pour voir les détails de cette commande.</p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: route.path } }"
          class="inline-block w-full rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>
    
    <PageStatus v-else :status="status === 'ready' ? 'ready' : status" :error-message="loadError">
      <article v-if="order" class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <!-- Header -->
          <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                Commande <span class="text-indigo-600">#{{ order.id.substring(0, 8) }}</span>
              </h1>
              <p class="mt-1 text-sm text-gray-500 flex items-center gap-2">
                <i class="fa-regular fa-calendar"></i> Passée le {{ formatDate(order.createdAt) }}
              </p>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span class="px-4 py-1.5 text-sm font-bold uppercase tracking-wider rounded-full bg-white border border-gray-200 shadow-sm text-gray-700">
                {{ orderStatusLabel(order.status) }}
              </span>
              <span v-if="payment" class="text-xs font-medium text-gray-500 flex items-center gap-1.5">
                <i class="fa-solid fa-credit-card"></i> Paiement : {{ paymentStatusLabel(payment.status) }}
              </span>
            </div>
          </div>

          <!-- Items -->
          <div class="p-6 md:p-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Articles commandés</h2>
            <ul class="divide-y divide-gray-100">
              <OrderLine v-for="item in order.items" :key="item.productId" :item="item" />
            </ul>
          </div>

          <!-- Footer/Summary -->
          <div class="p-6 md:p-8 bg-gray-50/50 border-t border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
              <div v-if="actionError" class="w-full md:w-auto p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-700 font-medium text-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i> {{ actionError }}
              </div>
              <div v-else class="hidden md:block"></div>

              <div class="w-full md:w-auto flex flex-col items-end">
                <p class="text-sm text-gray-500 mb-1">Total de la commande</p>
                <p class="text-3xl font-black text-gray-900">
                  <ProductPrice :price="order.total" />
                </p>
              </div>
            </div>

            <!-- Actions -->
            <div v-if="canPay || canCancel" class="mt-8 pt-8 border-t border-gray-200 flex flex-wrap gap-4 justify-end">
              <button
                v-if="canCancel"
                type="button"
                class="flex items-center gap-2 rounded-xl bg-white border border-gray-200 px-6 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors disabled:opacity-50"
                :disabled="pending"
                @click="onCancel"
              >
                <i class="fa-solid fa-xmark"></i> Annuler la commande
              </button>
              
              <button
                v-if="canPay"
                type="button"
                class="flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0"
                :disabled="pending"
                @click="onPay"
              >
                <i v-if="pending" class="fa-solid fa-circle-notch fa-spin"></i>
                <i v-else class="fa-solid fa-credit-card"></i>
                Procéder au paiement
              </button>
            </div>
          </div>
        </div>
      </article>
    </PageStatus>
  </section>
</template>
