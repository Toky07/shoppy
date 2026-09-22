<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { cartErrorMessage } from '@/modules/cart/ui/cartErrorMessage'

const props = defineProps<{
  productId: string
  stock: number
  variantId?: string
}>()

const session = inject(authSessionKey)
const cartState = inject(cartStateKey)

if (!session || !cartState) {
  throw new Error('Cart dependencies are not provided.')
}

const authSession = session
const state = cartState
const router = useRouter()
const route = useRoute()
const quantity = ref(1)
const pending = ref(false)
const errorMessage = ref<string>()
const successMessage = ref<string>()
const outOfStock = computed(() => props.stock <= 0)

async function onSubmit() {
  errorMessage.value = undefined
  successMessage.value = undefined

  if (!authSession.isAuthenticated.value) {
    await router.push({ path: '/login', query: { redirect: route.path } })
    return
  }

  pending.value = true
  try {
    await state.addItem(props.productId, quantity.value, props.variantId)
    successMessage.value = 'Ajouté au panier.'
  } catch (caught) {
    const error = toApiError(caught)

    if (error.code === 'unauthenticated') {
      authSession.clear()
      await router.push({ path: '/login', query: { redirect: route.path } })
      return
    }

    errorMessage.value = cartErrorMessage(error)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="flex flex-col gap-4" @submit.prevent="onSubmit">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div
        class="flex items-center justify-between gap-1 rounded-full border border-line bg-surface-inset p-1 sm:justify-start"
      >
        <button
          type="button"
          class="flex size-9 items-center justify-center rounded-full text-body transition-colors hover:bg-surface hover:text-strong disabled:opacity-40"
          aria-label="Diminuer la quantité"
          :disabled="outOfStock || pending || quantity <= 1"
          @click="quantity > 1 && quantity--"
        >
          <AppIcon name="minus" :size="15" />
        </button>
        <input
          v-model.number="quantity"
          class="numeric w-12 border-none bg-transparent text-center text-sm font-bold text-strong focus:outline-none"
          type="number"
          min="1"
          :max="stock"
          aria-label="Quantité"
          :disabled="outOfStock || pending"
        />
        <button
          type="button"
          class="flex size-9 items-center justify-center rounded-full text-body transition-colors hover:bg-surface hover:text-strong disabled:opacity-40"
          aria-label="Augmenter la quantité"
          :disabled="outOfStock || pending || quantity >= stock"
          @click="quantity < stock && quantity++"
        >
          <AppIcon name="plus" :size="15" />
        </button>
      </div>

      <button type="submit" class="btn-primary btn-lg flex-1" :disabled="outOfStock || pending">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="17" /></span>
        <AppIcon v-else name="cart" :size="17" />
        Ajouter au panier
      </button>
    </div>

    <div v-if="successMessage" role="status" class="notice-positive">
      <AppIcon name="check-circle" :size="17" class="mt-0.5" />
      <span>
        {{ successMessage }}
        <RouterLink to="/cart" class="font-semibold underline underline-offset-2">
          Voir mon panier
        </RouterLink>
      </span>
    </div>

    <div v-if="errorMessage" role="alert" class="notice-danger">
      <AppIcon name="alert-circle" :size="17" class="mt-0.5" />
      <span>{{ errorMessage }}</span>
    </div>
  </form>
</template>
