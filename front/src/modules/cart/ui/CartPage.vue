<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import PageHeader from '@/shared/ui/PageHeader.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import AuthRequiredPanel from '@/modules/auth/ui/AuthRequiredPanel.vue'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { cartStateKey } from '../application/cartStateKey'
import CartLine from './CartLine.vue'
import CartSummary from './CartSummary.vue'
import { cartErrorMessage } from './cartErrorMessage'

const session = inject(authSessionKey)
const cartState = inject(cartStateKey)

if (!session || !cartState) {
  throw new Error('Cart dependencies are not provided.')
}

const authSession = session
const state = cartState
const { pending, errorMessage: actionError, run } = usePendingAction((error) =>
  cartErrorMessage(error),
)
const checkoutOrderId = ref<string>()
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const cart = computed(() => state.cart.value)
const itemCount = computed(() =>
  (cart.value?.items ?? []).reduce((total, item) => total + item.quantity, 0),
)
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

async function runCart(action: () => Promise<unknown>) {
  checkoutOrderId.value = undefined
  await run(action)
}

function onUpdateQuantity(productId: string, quantity: number, variantId?: string | null) {
  return runCart(() => state.updateItem(productId, quantity, variantId))
}

function onRemove(productId: string, variantId?: string | null) {
  return runCart(() => state.removeItem(productId, variantId))
}

function onClear() {
  return runCart(() => state.clear())
}

async function onCheckout() {
  await runCart(async () => {
    const result = await state.checkout()
    checkoutOrderId.value = result.id
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <PageHeader
      eyebrow="Étape 1 sur 2"
      title="Votre Panier"
      icon="cart"
      description="Vérifiez vos articles, le paiement se fait à l'étape suivante."
    >
      <template #actions>
        <RouterLink to="/" class="btn-outline">
          <AppIcon name="arrow-left" :size="16" />
          Continuer mes achats
        </RouterLink>
      </template>
    </PageHeader>

    <AuthRequiredPanel
      v-if="!isAuthenticated"
      message="Vous devez être connecté pour accéder à votre panier et passer commande."
      redirect="/cart"
    />

    <template v-else>
      <StatusNotice v-if="checkoutOrderId" tone="positive" class="mt-10">
        <strong class="block font-display text-base">Commande confirmée</strong>
        <p class="mt-1">Votre commande n°{{ checkoutOrderId }} a été créée avec succès.</p>
        <RouterLink
          :to="{ name: 'order', params: { id: checkoutOrderId } }"
          class="mt-3 inline-flex items-center gap-1.5 font-semibold underline underline-offset-2"
        >
          Voir les détails de la commande
          <AppIcon name="arrow-right" :size="15" />
        </RouterLink>
      </StatusNotice>

      <StatusNotice v-if="actionError" tone="danger" class="mt-10">{{ actionError }}</StatusNotice>

      <div class="mt-10">
        <PageStatus :status="status" :error-message="loadError" skeleton="rows">
          <template #empty>
            <EmptyState
              icon="cart"
              title="Votre panier est vide"
              description="On dirait que vous n'avez pas encore trouvé votre bonheur. Jetez un œil aux nouveautés."
            >
              <RouterLink to="/" class="btn-primary btn-lg">
                Explorer le catalogue
                <AppIcon name="arrow-right" :size="16" />
              </RouterLink>
            </EmptyState>
          </template>

          <div v-if="cart && cart.items.length > 0" class="grid items-start gap-6 lg:grid-cols-3">
            <ul class="space-y-3 lg:col-span-2">
              <CartLine
                v-for="item in cart.items"
                :key="`${item.productId}:${item.variantId ?? ''}`"
                :item="item"
                @update-quantity="onUpdateQuantity(item.productId, $event, item.variantId)"
                @remove="onRemove(item.productId, item.variantId)"
              />
            </ul>

            <CartSummary
              :item-count="itemCount"
              :total="cart.total"
              :pending="pending"
              @checkout="onCheckout"
              @clear="onClear"
            />
          </div>
        </PageStatus>
      </div>
    </template>
  </section>
</template>
