<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { cartStateKey } from '../application/cartStateKey'
import CartLine from './CartLine.vue'
import { cartErrorMessage } from './cartErrorMessage'

const session = inject(authSessionKey)
const cartState = inject(cartStateKey)

if (!session || !cartState) {
  throw new Error('Cart dependencies are not provided.')
}

const authSession = session
const state = cartState
const pending = ref(false)
const actionError = ref<string>()
const checkoutOrderId = ref<string>()
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const cart = computed(() => state.cart.value)
const status = computed(() => {
  if (state.loading.value && !cart.value) {
    return 'loading'
  }
  if (state.error.value && !cart.value) {
    return 'error'
  }
  if (!cart.value || cart.value.items.length === 0) {
    return 'empty'
  }
  return 'ready'
})
const loadError = computed(() =>
  state.error.value ? cartErrorMessage(state.error.value) : undefined,
)

async function run(action: () => Promise<unknown>) {
  pending.value = true
  actionError.value = undefined
  checkoutOrderId.value = undefined

  try {
    await action()
  } catch (caught) {
    actionError.value = cartErrorMessage(toApiError(caught))
  } finally {
    pending.value = false
  }
}

function onUpdateQuantity(productId: string, quantity: number) {
  return run(() => state.updateItem(productId, quantity))
}

function onRemove(productId: string) {
  return run(() => state.removeItem(productId))
}

function onClear() {
  return run(() => state.clear())
}

async function onCheckout() {
  await run(async () => {
    const result = await state.checkout()
    checkoutOrderId.value = result.id
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <div class="mb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900">Votre Panier</h1>
      <p class="text-gray-500 mt-2">Vérifiez vos articles avant de passer commande.</p>
    </div>

    <template v-if="!isAuthenticated">
      <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center max-w-md mx-auto mt-12">
        <div class="h-20 w-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-600">
          <i class="fa-solid fa-lock text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Connexion requise</h2>
        <p class="text-gray-500 mb-8">Vous devez être connecté pour accéder à votre panier et passer commande.</p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: '/cart' } }"
          class="inline-block w-full rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>
    <template v-else>
      <div v-if="checkoutOrderId" class="mb-8 p-6 bg-green-50 border border-green-100 rounded-2xl flex items-start gap-4" role="status">
        <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 flex-shrink-0">
          <i class="fa-solid fa-check text-xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-green-900">Commande confirmée !</h3>
          <p class="text-green-700 mt-1">Votre commande n°{{ checkoutOrderId }} a été créée avec succès.</p>
          <RouterLink :to="{ name: 'order', params: { id: checkoutOrderId } }" class="inline-block mt-3 text-sm font-bold text-green-800 hover:text-green-600 underline">
            Voir les détails de la commande <i class="fa-solid fa-arrow-right ml-1"></i>
          </RouterLink>
        </div>
      </div>

      <div v-if="actionError" class="mb-8 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-700 font-medium" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i> {{ actionError }}
      </div>

      <PageStatus :status="status" :error-message="loadError">
        <template #empty>
          <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
            <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
              <i class="fa-solid fa-cart-arrow-down text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Votre panier est vide</h3>
            <p class="text-gray-500 mb-8 max-w-sm">On dirait que vous n'avez pas encore trouvé votre bonheur. Découvrez nos nouveautés !</p>
            <RouterLink to="/" class="rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
              Continuer mes achats
            </RouterLink>
          </div>
        </template>

        <div v-if="cart && cart.items.length > 0" class="grid lg:grid-cols-3 gap-8 items-start">
          <div class="lg:col-span-2">
            <ul class="space-y-4">
              <CartLine
                v-for="item in cart.items"
                :key="item.productId"
                :item="item"
                @update-quantity="onUpdateQuantity(item.productId, $event)"
                @remove="onRemove(item.productId)"
              />
            </ul>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm sticky top-24">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Résumé de la commande</h2>
            
            <div class="space-y-4 text-sm text-gray-600 mb-6">
              <div class="flex justify-between">
                <span>Sous-total ({{ cart.items.length }} articles)</span>
                <span class="font-medium text-gray-900"><ProductPrice :price="cart.total" /></span>
              </div>
              <div class="flex justify-between">
                <span>Frais de livraison</span>
                <span class="text-green-600 font-medium">Gratuit</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-6 mb-8">
              <div class="flex justify-between items-center">
                <span class="text-base font-bold text-gray-900">Total TTC</span>
                <span class="text-2xl font-black text-gray-900"><ProductPrice :price="cart.total" /></span>
              </div>
            </div>

            <div class="flex flex-col gap-3">
              <button
                type="button"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-4 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0"
                :disabled="pending"
                @click="onCheckout"
              >
                <i v-if="pending" class="fa-solid fa-circle-notch fa-spin"></i>
                <i v-else class="fa-solid fa-lock"></i>
                Payer ma commande
              </button>
              
              <button
                type="button"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-white border border-gray-200 px-6 py-3.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50"
                :disabled="pending"
                @click="onClear"
              >
                <i class="fa-regular fa-trash-can"></i>
                Vider le panier
              </button>
            </div>
            
            <div class="mt-6 flex items-center justify-center gap-4 text-gray-400">
              <i class="fa-brands fa-cc-visa text-2xl"></i>
              <i class="fa-brands fa-cc-mastercard text-2xl"></i>
              <i class="fa-brands fa-cc-paypal text-2xl"></i>
              <i class="fa-brands fa-cc-apple-pay text-2xl"></i>
            </div>
          </div>
        </div>
      </PageStatus>
    </template>
  </section>
</template>
