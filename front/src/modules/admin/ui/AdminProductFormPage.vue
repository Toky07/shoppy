<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { toApiError } from '@/shared/http/toApiError'
import { centsToEuros, eurosToCents } from '@/shared/money/euros'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import AdminGate from './AdminGate.vue'

const catalog = inject(catalogRepositoryKey)
const adminCatalog = inject(adminCatalogRepositoryKey)

if (!catalog || !adminCatalog) {
  throw new Error('Admin catalog dependencies are not provided.')
}

const catalogRepository = catalog
const adminRepository = adminCatalog
const router = useRouter()
const route = useRoute()
const isCreate = computed(() => route.name === 'admin-product-new')
const productId = computed(() => String(route.params.id ?? ''))
const name = ref('')
const priceEuros = ref(0)
const description = ref('')
const stock = ref(0)
const imageUrl = ref('')
const pending = ref(false)
const errorMessage = ref<string>()
const loadError = ref<string>()

watch(
  [isCreate, productId],
  async () => {
    loadError.value = undefined
    if (isCreate.value) {
      name.value = ''
      priceEuros.value = 0
      description.value = ''
      stock.value = 0
      imageUrl.value = ''
      return
    }

    try {
      const product = await catalogRepository.getById(productId.value)
      name.value = product.name
      priceEuros.value = centsToEuros(product.price.cents)
      description.value = product.description ?? ''
      stock.value = product.stock
      imageUrl.value = product.imageUrl ?? ''
    } catch (caught) {
      const error = toApiError(caught)
      loadError.value = error.code === 'product_not_found' ? 'Ce produit est introuvable.' : error.message
    }
  },
  { immediate: true },
)

async function onSubmit() {
  pending.value = true
  errorMessage.value = undefined
  try {
    const descriptionValue = description.value.trim() === '' ? null : description.value.trim()
    const imageValue = imageUrl.value.trim() === '' ? null : imageUrl.value.trim()
    if (isCreate.value) {
      await adminRepository.create({
        name: name.value.trim(),
        priceCents: eurosToCents(priceEuros.value),
        description: descriptionValue,
        stock: stock.value,
        imageUrl: imageValue,
      })
    } else {
      await adminRepository.update(productId.value, {
        name: name.value.trim(),
        priceCents: eurosToCents(priceEuros.value),
        description: descriptionValue,
      })
      await adminRepository.setStock(productId.value, stock.value)
    }
    await router.push('/admin/products')
  } catch (caught) {
    errorMessage.value = toApiError(caught).violations?.[0]?.message ?? toApiError(caught).message
  } finally {
    pending.value = false
  }
}

async function onDelete() {
  pending.value = true
  errorMessage.value = undefined
  try {
    await adminRepository.delete(productId.value)
    await router.push('/admin/products')
  } catch (caught) {
    errorMessage.value = toApiError(caught).message
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <section>
    <p class="mb-6 text-sm">
      <RouterLink to="/admin/products" class="text-stone-600 hover:text-stone-900">Retour au catalogue</RouterLink>
    </p>
    <h1 class="text-2xl font-semibold tracking-tight">
      {{ isCreate ? 'Nouveau produit' : 'Modifier le produit' }}
    </h1>
    <AdminGate :redirect="route.path">
      <p v-if="loadError" class="mt-6" role="alert">{{ loadError }}</p>
      <form v-else class="mt-6 max-w-md space-y-4" @submit.prevent="onSubmit">
        <p v-if="errorMessage" role="alert" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-800">
          {{ errorMessage }}
        </p>
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Nom</span>
          <input v-model="name" required class="w-full rounded-md border border-stone-300 px-3 py-2" />
        </label>
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Prix (€)</span>
          <input
            v-model.number="priceEuros"
            type="number"
            min="0"
            step="0.01"
            required
            class="w-full rounded-md border border-stone-300 px-3 py-2"
          />
        </label>
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Description</span>
          <textarea v-model="description" rows="3" class="w-full rounded-md border border-stone-300 px-3 py-2" />
        </label>
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Stock</span>
          <input
            v-model.number="stock"
            type="number"
            min="0"
            step="1"
            required
            class="w-full rounded-md border border-stone-300 px-3 py-2"
          />
        </label>
        <label v-if="isCreate" class="block text-sm">
          <span class="mb-1 block text-stone-600">Image (URL)</span>
          <input v-model="imageUrl" class="w-full rounded-md border border-stone-300 px-3 py-2" />
        </label>
        <div class="flex flex-wrap gap-3">
          <button
            type="submit"
            class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800 disabled:opacity-50"
            :disabled="pending"
          >
            {{ isCreate ? 'Créer' : 'Enregistrer' }}
          </button>
          <button
            v-if="!isCreate"
            type="button"
            class="rounded-md border border-stone-300 px-4 py-2 text-sm hover:bg-stone-50 disabled:opacity-50"
            :disabled="pending"
            @click="onDelete"
          >
            Supprimer
          </button>
        </div>
      </form>
    </AdminGate>
  </section>
</template>
