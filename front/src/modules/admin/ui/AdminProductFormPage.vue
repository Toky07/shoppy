<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { toApiError } from '@/shared/http/toApiError'
import { centsToEuros, eurosToCents } from '@/shared/money/euros'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import { usePendingAction } from '@/shared/async/usePendingAction'
import AdminPageHeader from './AdminPageHeader.vue'
import AdminProductForm from './AdminProductForm.vue'
import { emptyProductDraft } from './productDraft'
import type { Category } from '@/modules/catalog/domain/Category'

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
const draft = ref(emptyProductDraft())
const categories = ref<Category[]>([])
const previewImages = ref<string[]>([])
const slug = ref<string | null>(null)
const loadError = ref<string>()
const { pending, errorMessage, run } = usePendingAction(
  (error) => error.violations[0]?.message ?? error.message,
)

watch(
  [isCreate, productId],
  async () => {
    loadError.value = undefined
    if (isCreate.value) {
      draft.value = emptyProductDraft()
      previewImages.value = []
      slug.value = null
      categories.value = await catalogRepository.listCategories()
      return
    }

    try {
      categories.value = await catalogRepository.listCategories()
      const product = await catalogRepository.getById(productId.value)
      draft.value = {
        name: product.name,
        priceEuros: centsToEuros(product.price.cents),
        description: product.description ?? '',
        stock: product.stock,
        categoryId: product.category?.id ?? '',
      }
      previewImages.value = product.imageUrls
      slug.value = product.slug
    } catch (caught) {
      const error = toApiError(caught)
      loadError.value =
        error.code === 'product_not_found' ? 'Ce produit est introuvable.' : error.message
    }
  },
  { immediate: true },
)

function onSubmit() {
  return run(async () => {
    const descriptionValue = draft.value.description.trim() === '' ? null : draft.value.description.trim()
    const categoryId = draft.value.categoryId === '' ? null : draft.value.categoryId
    if (isCreate.value) {
      await adminRepository.create({
        name: draft.value.name.trim(),
        priceCents: eurosToCents(draft.value.priceEuros),
        description: descriptionValue,
        stock: draft.value.stock,
        categoryId,
      })
    } else {
      await adminRepository.update(productId.value, {
        name: draft.value.name.trim(),
        priceCents: eurosToCents(draft.value.priceEuros),
        description: descriptionValue,
        categoryId,
      })
      await adminRepository.setStock(productId.value, draft.value.stock)
    }
    await router.push('/admin/products')
  })
}

function onDelete() {
  const name = draft.value.name.trim() || 'ce produit'
  if (!window.confirm(`Supprimer « ${name} » du catalogue ? Cette action est définitive.`)) {
    return
  }

  return run(async () => {
    await adminRepository.delete(productId.value)
    await router.push('/admin/products')
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      :title="isCreate ? 'Nouveau produit' : draft.name.trim() || 'Modifier le produit'"
      :icon="isCreate ? 'plus' : 'settings'"
      :description="
        isCreate
          ? 'Composez la fiche, le prix et le stock. L’aperçu boutique se met à jour en direct.'
          : 'Ajustez la fiche, le prix et le stock. L’aperçu boutique reflète vos changements.'
      "
      back-to="/admin/products"
      back-label="Retour au catalogue"
    />

    <StatusNotice v-if="loadError" tone="danger" class="mt-6 max-w-xl">{{ loadError }}</StatusNotice>

    <AdminProductForm
      v-else
      v-model="draft"
      :is-create="isCreate"
      :pending="pending"
      :error-message="errorMessage"
      :preview-images="previewImages"
      :slug="slug"
      :categories="categories"
      @submit="onSubmit"
      @delete="onDelete"
    />
  </section>
</template>
