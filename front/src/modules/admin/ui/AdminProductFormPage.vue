<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { toApiError } from '@/shared/http/toApiError'
import { centsToEuros, eurosToCents } from '@/shared/money/euros'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import AdminGate from './AdminGate.vue'
import AdminPageHeader from './AdminPageHeader.vue'

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
      loadError.value =
        error.code === 'product_not_found' ? 'Ce produit est introuvable.' : error.message
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
  <section class="animate-fade-in">
    <AdminPageHeader
      eyebrow="Catalogue"
      :title="isCreate ? 'Nouveau produit' : 'Modifier le produit'"
      :icon="isCreate ? 'plus' : 'settings'"
      back-to="/admin/products"
      back-label="Retour au catalogue"
    />

    <AdminGate :redirect="route.path">
      <div v-if="loadError" role="alert" class="notice-danger mt-10 max-w-xl">
        <AppIcon name="alert-circle" :size="18" class="mt-0.5" />
        <span>{{ loadError }}</span>
      </div>

      <form v-else class="panel mt-10 max-w-xl space-y-6 p-6 sm:p-8" @submit.prevent="onSubmit">
        <div v-if="errorMessage" role="alert" class="notice-danger">
          <AppIcon name="alert-circle" :size="18" class="mt-0.5" />
          <span>{{ errorMessage }}</span>
        </div>

        <label class="block">
          <span class="field-label">Nom</span>
          <input v-model="name" required class="field" placeholder="Nuvora Tee" />
        </label>

        <div class="grid gap-6 sm:grid-cols-2">
          <label class="block">
            <span class="field-label">Prix (€)</span>
            <input
              v-model.number="priceEuros"
              type="number"
              min="0"
              step="0.01"
              required
              class="field numeric"
            />
          </label>

          <label class="block">
            <span class="field-label">Stock</span>
            <input
              v-model.number="stock"
              type="number"
              min="0"
              step="1"
              required
              class="field numeric"
            />
          </label>
        </div>

        <label class="block">
          <span class="field-label">Description</span>
          <textarea
            v-model="description"
            rows="4"
            class="field resize-y"
            placeholder="Ce qui rend ce produit utile, en deux phrases."
          />
        </label>

        <label v-if="isCreate" class="block">
          <span class="field-label">Image (URL)</span>
          <input v-model="imageUrl" class="field" placeholder="/media/products/mon-produit.svg" />
        </label>

        <div class="flex flex-wrap gap-3 border-t border-line pt-6">
          <button type="submit" class="btn-primary" :disabled="pending">
            <AppIcon :name="isCreate ? 'plus' : 'check'" :size="16" />
            {{ isCreate ? 'Créer' : 'Enregistrer' }}
          </button>
          <button
            v-if="!isCreate"
            type="button"
            class="btn-danger"
            :disabled="pending"
            @click="onDelete"
          >
            <AppIcon name="trash" :size="15" />
            Supprimer
          </button>
        </div>
      </form>
    </AdminGate>
  </section>
</template>
