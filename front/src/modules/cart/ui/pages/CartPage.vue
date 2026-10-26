<script setup lang="ts">
import { computed, inject, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import PageHeader from '@/shared/ui/PageHeader.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { cartStateKey } from '../../application/cartStateKey'
import CartLine from '../components/CartLine.vue'
import CartSummary from '../components/CartSummary.vue'
import CheckoutAddressForm from '../components/CheckoutAddressForm.vue'
import CheckoutShippingForm from '../components/CheckoutShippingForm.vue'
import { cartErrorMessage } from '../lib/cartErrorMessage'
import { emptyAddressDraft, toPostalAddress, type AddressDraft } from '@/modules/order/domain/PostalAddress'
import { quoteShipping, type ShippingMethodCode } from '@/modules/order/domain/ShippingMethod'

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
const addressError = ref<string>()
const guestEmail = ref('')
let shipping = reactive<AddressDraft>(emptyAddressDraft())
let billing = reactive<AddressDraft>(emptyAddressDraft())
const billingSameAsShipping = ref(true)
const shippingMethod = ref<ShippingMethodCode>('standard')
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const cart = computed(() => state.cart.value)
const quotedShipping = computed(() => quoteShipping(shippingMethod.value, cart.value?.total.cents ?? 0))
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

function addressReady(draft: AddressDraft): boolean {
  return (
    draft.recipient.trim() !== '' &&
    draft.line1.trim() !== '' &&
    draft.postalCode.trim() !== '' &&
    draft.city.trim() !== '' &&
    draft.country.trim() !== ''
  )
}

async function onCheckout() {
  if (!addressReady(shipping) || (!billingSameAsShipping.value && !addressReady(billing))) {
    addressError.value = 'Renseignez l’adresse de livraison et de facturation.'
    return
  }

  if (!isAuthenticated.value && guestEmail.value.trim() === '') {
    addressError.value = 'Indiquez un e-mail pour recevoir la commande.'
    return
  }

  addressError.value = undefined
  await runCart(async () => {
    const result = await state.checkout({
      shippingAddress: toPostalAddress(shipping),
      billingSameAsShipping: billingSameAsShipping.value,
      billingAddress: billingSameAsShipping.value ? undefined : toPostalAddress(billing),
      shippingMethod: shippingMethod.value,
      ...(isAuthenticated.value ? {} : { email: guestEmail.value.trim() }),
    })
    if (result.user) {
      authSession.set({ user: result.user })
    }
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

    <StatusNotice v-if="checkoutOrderId" tone="positive" class="mt-10">
        <strong class="block font-display text-base">Commande créée</strong>
        <p class="mt-1">
          La commande n°{{ checkoutOrderId }} est enregistrée. Le paiement se fait sur sa page.
        </p>
        <RouterLink
          :to="{ name: 'order', params: { id: checkoutOrderId } }"
          class="mt-3 inline-flex items-center gap-1.5 font-semibold underline underline-offset-2"
        >
          Payer la commande
          <AppIcon name="arrow-right" :size="15" />
        </RouterLink>
      </StatusNotice>

      <StatusNotice v-if="addressError" tone="danger" class="mt-10">{{ addressError }}</StatusNotice>
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
              <li v-if="!isAuthenticated">
                <section class="panel p-6" aria-labelledby="checkout-email-title">
                  <h2 id="checkout-email-title" class="font-display text-lg font-bold text-strong">
                    Vos coordonnées
                  </h2>
                  <p class="mt-1 text-sm text-muted">
                    Pas besoin de compte. Un e-mail suffit pour suivre le paiement.
                  </p>
                  <label class="mt-5 block">
                    <span class="field-label">E-mail</span>
                    <input
                      v-model="guestEmail"
                      class="field"
                      type="email"
                      autocomplete="email"
                      required
                    />
                  </label>
                </section>
              </li>
              <li>
                <CheckoutAddressForm
                  v-model:shipping="shipping"
                  v-model:billing="billing"
                  v-model:billing-same-as-shipping="billingSameAsShipping"
                />
              </li>
              <li>
                <CheckoutShippingForm v-model="shippingMethod" />
              </li>
            </ul>

            <CartSummary
              :item-count="itemCount"
              :total="cart.total"
              :shipping-label="quotedShipping.label"
              :shipping-fee-cents="quotedShipping.fee.cents"
              :pending="pending"
              action-label="Valider ma commande"
              :note="
                isAuthenticated
                  ? 'La commande est créée ici. Le paiement se fait sur la page suivante.'
                  : 'Un e-mail suffit. Le paiement se fait sur la page suivante.'
              "
              @checkout="onCheckout"
              @clear="onClear"
            />
          </div>
        </PageStatus>
      </div>
  </section>
</template>
