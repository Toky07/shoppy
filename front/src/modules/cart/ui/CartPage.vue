<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
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
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div>
        <span class="badge-neutral"><AppIcon name="cart" :size="13" /> Étape 1 sur 2</span>
        <h1 class="display-tight mt-5 text-4xl text-strong sm:text-5xl">Votre Panier</h1>
        <p class="mt-3 text-sm text-muted">
          Vérifiez vos articles, le paiement se fait à l'étape suivante.
        </p>
      </div>
      <RouterLink to="/" class="btn-outline">
        <AppIcon name="arrow-left" :size="16" />
        Continuer mes achats
      </RouterLink>
    </div>

    <template v-if="!isAuthenticated">
      <div class="panel mx-auto mt-12 max-w-md p-8 text-center">
        <div
          class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
        >
          <AppIcon name="lock" :size="24" />
        </div>
        <h2 class="text-xl font-bold text-strong">Connexion requise</h2>
        <p class="mt-3 text-sm text-muted">
          Vous devez être connecté pour accéder à votre panier et passer commande.
        </p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: '/cart' } }"
          class="btn-primary btn-lg mt-8 w-full"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>

    <template v-else>
      <div v-if="checkoutOrderId" role="status" class="notice-positive mt-10">
        <AppIcon name="check-circle" :size="20" class="mt-0.5" />
        <div>
          <strong class="block font-display text-base">Commande confirmée</strong>
          <p class="mt-1">Votre commande n°{{ checkoutOrderId }} a été créée avec succès.</p>
          <RouterLink
            :to="{ name: 'order', params: { id: checkoutOrderId } }"
            class="mt-3 inline-flex items-center gap-1.5 font-semibold underline underline-offset-2"
          >
            Voir les détails de la commande
            <AppIcon name="arrow-right" :size="15" />
          </RouterLink>
        </div>
      </div>

      <div v-if="actionError" role="alert" class="notice-danger mt-10">
        <AppIcon name="alert-circle" :size="18" class="mt-0.5" />
        <span>{{ actionError }}</span>
      </div>

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
                :key="item.productId"
                :item="item"
                @update-quantity="onUpdateQuantity(item.productId, $event)"
                @remove="onRemove(item.productId)"
              />
            </ul>

            <div class="panel sticky top-28 p-6">
              <h2 class="font-display text-lg font-bold text-strong">Résumé</h2>

              <dl class="mt-6 space-y-3 text-sm">
                <div class="flex items-baseline justify-between gap-4">
                  <dt class="text-muted">
                    Sous-total ({{ itemCount }} article{{ itemCount > 1 ? 's' : '' }})
                  </dt>
                  <dd class="numeric font-semibold text-strong">
                    <ProductPrice :price="cart.total" />
                  </dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                  <dt class="text-muted">Livraison</dt>
                  <dd class="font-semibold text-positive">Offerte</dd>
                </div>
              </dl>

              <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-line pt-6">
                <span class="font-semibold text-strong">Total TTC</span>
                <span class="numeric font-display text-2xl font-extrabold text-strong">
                  <ProductPrice :price="cart.total" />
                </span>
              </div>

              <div class="mt-7 flex flex-col gap-2.5">
                <button
                  type="button"
                  class="btn-primary btn-lg w-full"
                  :disabled="pending"
                  @click="onCheckout"
                >
                  <span v-if="pending" class="animate-orbit">
                    <AppIcon name="loader" :size="17" />
                  </span>
                  <AppIcon v-else name="lock" :size="16" />
                  Payer ma commande
                </button>
                <button type="button" class="btn-ghost w-full" :disabled="pending" @click="onClear">
                  <AppIcon name="trash" :size="15" />
                  Vider le panier
                </button>
              </div>

              <p class="mt-6 flex items-center justify-center gap-2 text-center text-[0.7rem] text-faint">
                <AppIcon name="shield" :size="14" />
                Paiement chiffré, aucune donnée bancaire stockée
              </p>
            </div>
          </div>
        </PageStatus>
      </div>
    </template>
  </section>
</template>
