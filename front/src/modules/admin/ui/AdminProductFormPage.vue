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
import AdminProductGallery, { type GalleryImage } from './AdminProductGallery.vue'
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
const persistedId = ref<string | null>(null)
const editingId = computed(() => persistedId.value ?? (isCreate.value ? '' : productId.value))
const queueImages = computed(() => editingId.value === '')
const draft = ref(emptyProductDraft())
const categories = ref<Category[]>([])
const images = ref<GalleryImage[]>([])
const slug = ref<string | null>(null)
const loadError = ref<string>()
const { pending, errorMessage, run } = usePendingAction(
  (error) => error.violations[0]?.message ?? error.message,
)
const previewImages = computed(() => images.value.map((image) => image.url))

function releaseQueuedUrls() {
  for (const image of images.value) {
    if (image.file) {
      URL.revokeObjectURL(image.url)
    }
  }
}

watch(
  [isCreate, productId],
  async () => {
    loadError.value = undefined
    persistedId.value = null
    releaseQueuedUrls()
    if (isCreate.value) {
      draft.value = emptyProductDraft()
      images.value = []
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
        sku: product.sku,
        variants: product.variants.map((variant) => ({
          id: variant.id,
          sku: variant.sku,
          size: variant.size ?? '',
          color: variant.color ?? '',
          stock: variant.stock,
        })),
        published: product.published,
      }
      const media = await adminRepository.listImages(product.id)
      images.value = media.map((image) => ({ key: image.id, url: image.url, id: image.id }))
      slug.value = product.slug
    } catch (caught) {
      const error = toApiError(caught)
      loadError.value =
        error.code === 'product_not_found' ? 'Ce produit est introuvable.' : error.message
    }
  },
  { immediate: true },
)

async function uploadQueued(productIdValue: string) {
  const pendingFiles = images.value.filter((image) => image.file)
  if (pendingFiles.length === 0) {
    return
  }

  const uploaded = []
  for (const [position, image] of pendingFiles.entries()) {
    if (!image.file) {
      continue
    }
    uploaded.push(await adminRepository.uploadImage(productIdValue, image.file, position))
    URL.revokeObjectURL(image.url)
  }
  images.value = uploaded.map((image) => ({ key: image.id, url: image.url, id: image.id }))
}

function onSubmit() {
  return run(async () => {
    const descriptionValue = draft.value.description.trim() === '' ? null : draft.value.description.trim()
    const categoryId = draft.value.categoryId === '' ? null : draft.value.categoryId
    const sku = draft.value.sku.trim()
    const variants = draft.value.variants
      .filter((variant) => variant.sku.trim() !== '' || variant.size.trim() !== '' || variant.color.trim() !== '')
      .map((variant) => ({
        ...(variant.id === '' ? {} : { id: variant.id }),
        sku: variant.sku.trim(),
        size: variant.size.trim() === '' ? null : variant.size.trim(),
        color: variant.color.trim() === '' ? null : variant.color.trim(),
        stock: Number(variant.stock) || 0,
      }))
    const fields = {
      name: draft.value.name.trim(),
      priceCents: eurosToCents(draft.value.priceEuros),
      description: descriptionValue,
      categoryId,
      published: draft.value.published,
      ...(sku === '' ? {} : { sku }),
    }
    if (editingId.value === '') {
      const created = await adminRepository.create({
        ...fields,
        stock: draft.value.stock,
        ...(variants.length === 0 ? {} : { variants }),
      })
      persistedId.value = created.id
      await uploadQueued(created.id)
    } else {
      await adminRepository.update(editingId.value, {
        ...fields,
        variants,
      })
      if (variants.length === 0) {
        await adminRepository.setStock(editingId.value, draft.value.stock)
      }
      await uploadQueued(editingId.value)
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
    await adminRepository.delete(editingId.value)
    await router.push('/admin/products')
  })
}

function onAddImages(files: File[]) {
  if (queueImages.value) {
    images.value = [
      ...images.value,
      ...files.map((file) => ({
        key: crypto.randomUUID(),
        url: URL.createObjectURL(file),
        id: null,
        file,
      })),
    ]
    return
  }

  return run(async () => {
    const start = images.value.length
    for (const [offset, file] of files.entries()) {
      const uploaded = await adminRepository.uploadImage(editingId.value, file, start + offset)
      images.value = [...images.value, { key: uploaded.id, url: uploaded.url, id: uploaded.id }]
    }
  })
}

function onRemoveImage(index: number) {
  const image = images.value[index]
  if (!image) {
    return
  }

  if (image.id === null) {
    if (image.file) {
      URL.revokeObjectURL(image.url)
    }
    images.value = images.value.filter((_, current) => current !== index)
    return
  }

  return run(async () => {
    await adminRepository.deleteImage(image.id as string)
    images.value = images.value.filter((item) => item.id !== image.id)
  })
}

function onMoveImage(index: number, direction: -1 | 1) {
  const nextIndex = index + direction
  if (nextIndex < 0 || nextIndex >= images.value.length) {
    return
  }

  const next = images.value.slice()
  const [moved] = next.splice(index, 1)
  if (!moved) {
    return
  }
  next.splice(nextIndex, 0, moved)
  images.value = next

  if (next.some((image) => image.id === null)) {
    return
  }

  return run(async () => {
    await adminRepository.reorderImages(
      editingId.value,
      next.map((image) => image.id as string),
    )
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
      :is-create="queueImages"
      :pending="pending"
      :error-message="errorMessage"
      :preview-images="previewImages"
      :slug="slug"
      :categories="categories"
      @submit="onSubmit"
      @delete="onDelete"
    >
      <template #media>
        <AdminProductGallery
          :images="images"
          :pending="pending"
          :queued="queueImages"
          @add="onAddImages"
          @remove="onRemoveImage"
          @move="onMoveImage"
        />
      </template>
    </AdminProductForm>
  </section>
</template>
