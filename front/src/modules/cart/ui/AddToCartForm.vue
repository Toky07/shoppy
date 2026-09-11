<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { cartErrorMessage } from '@/modules/cart/ui/cartErrorMessage'

const props = defineProps<{
  productId: string
  stock: number
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
    await state.addItem(props.productId, quantity.value)
    successMessage.value = 'Ajouté au panier.'
  } catch (caught) {
    errorMessage.value = cartErrorMessage(toApiError(caught))
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="flex flex-col gap-4" @submit.prevent="onSubmit">
    <div class="flex items-center gap-4">
      <div class="flex items-center border border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm">
        <button 
          type="button" 
          class="px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-indigo-600 transition-colors disabled:opacity-50"
          @click="quantity > 1 && quantity--"
          :disabled="outOfStock || pending || quantity <= 1"
        >
          <i class="fa-solid fa-minus text-xs"></i>
        </button>
        <input
          v-model.number="quantity"
          class="w-12 text-center font-semibold text-gray-900 border-none focus:ring-0 p-0"
          type="number"
          min="1"
          :max="stock"
          :disabled="outOfStock || pending"
        />
        <button 
          type="button" 
          class="px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-indigo-600 transition-colors disabled:opacity-50"
          @click="quantity < stock && quantity++"
          :disabled="outOfStock || pending || quantity >= stock"
        >
          <i class="fa-solid fa-plus text-xs"></i>
        </button>
      </div>

      <button
        type="submit"
        class="flex-grow flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none"
        :disabled="outOfStock || pending"
      >
        <i v-if="pending" class="fa-solid fa-circle-notch fa-spin"></i>
        <i v-else class="fa-solid fa-cart-plus"></i>
        Ajouter au panier
      </button>
    </div>

    <div v-if="successMessage" role="status" class="flex items-center gap-2 text-sm font-medium text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-100">
      <i class="fa-solid fa-circle-check"></i> {{ successMessage }}
    </div>
    <div v-if="errorMessage" role="alert" class="flex items-center gap-2 text-sm font-medium text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-100">
      <i class="fa-solid fa-circle-exclamation"></i> {{ errorMessage }}
    </div>
  </form>
</template>
