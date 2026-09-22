<script setup lang="ts">
import { computed, watch } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import ImageCarousel from '@/shared/ui/ImageCarousel.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { formatMoney } from '@/shared/money/formatMoney'
import { stockLabel } from '@/modules/catalog/ui/stockLabel'
import type { Category } from '@/modules/catalog/domain/Category'
import type { ProductDraft } from './productDraft'

const props = defineProps<{
  isCreate: boolean
  pending: boolean
  errorMessage?: string
  previewImages?: string[]
  slug?: string | null
  categories?: Category[]
}>()

const draft = defineModel<ProductDraft>({ required: true })

const emit = defineEmits<{
  submit: []
  delete: []
}>()

const previewPrice = computed(() =>
  formatMoney({ cents: Math.round((Number(draft.value.priceEuros) || 0) * 100), currency: 'EUR' }),
)
const previewName = computed(() => draft.value.name.trim() || 'Sans nom')
const gallery = computed(() => props.previewImages ?? [])
const stockTone = computed(() => (Number(draft.value.stock) > 0 ? 'badge-positive' : 'badge-danger'))
const hasVariants = computed(() => draft.value.variants.length > 0)

watch(
  () => draft.value.variants,
  (variants) => {
    if (variants.length === 0) {
      return
    }

    draft.value.stock = variants.reduce((sum, variant) => sum + (Number(variant.stock) || 0), 0)
  },
  { deep: true },
)

function addVariant() {
  draft.value.variants.push({ id: '', sku: '', size: '', color: '', stock: 0 })
}

function removeVariant(index: number) {
  draft.value.variants.splice(index, 1)
}
</script>

<template>
  <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
    <form class="overflow-hidden rounded-[1.4rem] border border-line bg-surface shadow-soft" @submit.prevent="emit('submit')">
      <div class="border-b border-line bg-surface-muted px-6 py-5 sm:px-8">
        <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">Studio</p>
        <h2 class="mt-1 font-display text-xl font-bold text-strong">
          {{ isCreate ? 'Composer la fiche' : 'Mettre à jour la fiche' }}
        </h2>
        <p class="mt-1 text-sm text-muted">
          Nom, description, prix et stock — l’aperçu boutique se met à jour à droite.
        </p>
      </div>

      <div class="space-y-8 p-6 sm:p-8">
        <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>

        <section>
          <p class="text-[0.7rem] font-semibold tracking-[0.14em] text-muted uppercase">Identité</p>
          <label class="mt-4 block">
            <span class="field-label">Nom</span>
            <input v-model="draft.name" required class="field font-display text-lg font-semibold" placeholder="Nuvora Tee" />
          </label>
          <label class="mt-5 block">
            <span class="field-label">Description</span>
            <textarea
              v-model="draft.description"
              rows="6"
              class="field resize-y leading-relaxed"
              placeholder="Ce qui rend ce produit utile, en deux phrases."
            />
          </label>
          <label v-if="(props.categories ?? []).length > 0" class="mt-5 block">
            <span class="field-label">Catégorie</span>
            <select v-model="draft.categoryId" class="field">
              <option value="">Aucune</option>
              <option v-for="category in props.categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>
          <label class="mt-5 block">
            <span class="field-label">SKU</span>
            <input v-model="draft.sku" class="field uppercase" placeholder="NUVORA-TEE" maxlength="40" />
          </label>
        </section>

        <section class="border-t border-line pt-8">
          <p class="text-[0.7rem] font-semibold tracking-[0.14em] text-muted uppercase">Inventaire</p>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="block rounded-2xl border border-line bg-surface-muted p-4">
              <span class="flex items-center gap-2 text-xs font-semibold tracking-[0.12em] text-muted uppercase">
                <AppIcon name="tag" :size="14" />
                Prix (€)
              </span>
              <input
                v-model.number="draft.priceEuros"
                type="number"
                min="0"
                step="0.01"
                required
                class="field numeric mt-3 border-transparent bg-surface text-xl font-extrabold"
              />
            </label>
            <label class="block rounded-2xl border border-line bg-surface-muted p-4">
              <span class="flex items-center gap-2 text-xs font-semibold tracking-[0.12em] text-muted uppercase">
                <AppIcon name="package" :size="14" />
                Stock
              </span>
              <input
                v-model.number="draft.stock"
                type="number"
                min="0"
                step="1"
                required
                :disabled="hasVariants"
                class="field numeric mt-3 border-transparent bg-surface text-xl font-extrabold disabled:opacity-60"
              />
            </label>
          </div>
          <div class="mt-5">
            <div class="flex items-center justify-between gap-3">
              <p class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">Variantes</p>
              <button type="button" class="btn-outline" @click="addVariant">Ajouter une variante</button>
            </div>
            <p v-if="draft.variants.length === 0" class="mt-3 text-sm text-muted">
              Sans variante, le stock ci-dessus est celui du produit.
            </p>
            <div v-for="(variant, index) in draft.variants" :key="`${variant.id}-${index}`" class="mt-3 grid gap-3 rounded-2xl border border-line bg-surface-muted p-4 sm:grid-cols-4">
              <label class="block">
                <span class="field-label">Taille</span>
                <input v-model="variant.size" class="field" placeholder="M" />
              </label>
              <label class="block">
                <span class="field-label">Couleur</span>
                <input v-model="variant.color" class="field" placeholder="Noir" />
              </label>
              <label class="block">
                <span class="field-label">SKU</span>
                <input v-model="variant.sku" class="field uppercase" placeholder="NUVORA-TEE-M" />
              </label>
              <label class="block">
                <span class="field-label">Stock</span>
                <input v-model.number="variant.stock" type="number" min="0" step="1" class="field numeric" />
              </label>
              <button type="button" class="btn-danger sm:col-span-4" @click="removeVariant(index)">
                Retirer
              </button>
            </div>
          </div>
        </section>
      </div>

      <div class="flex flex-wrap items-center gap-3 border-t border-line bg-surface-muted px-6 py-5 sm:px-8">
        <button type="submit" class="btn-primary" :disabled="pending">
          <AppIcon :name="isCreate ? 'plus' : 'check'" :size="16" />
          {{ isCreate ? 'Créer' : 'Enregistrer' }}
        </button>
        <button
          v-if="!isCreate"
          type="button"
          class="btn-danger"
          :disabled="pending"
          @click="emit('delete')"
        >
          <AppIcon name="trash" :size="15" />
          Supprimer
        </button>
      </div>
    </form>

    <aside class="xl:sticky xl:top-24">
      <p class="mb-3 text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">Vitrine boutique</p>
      <div class="overflow-hidden rounded-[1.4rem] border border-line bg-surface shadow-float">
        <div class="aspect-4/5 bg-surface-inset">
          <ImageCarousel :images="gallery" :alt="previewName" />
        </div>
        <div class="space-y-3 p-5">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="font-display text-xl font-bold text-strong">{{ previewName }}</h2>
              <p v-if="slug" class="mt-1 truncate text-xs text-faint">/{{ slug }}</p>
            </div>
            <span :class="stockTone">{{ stockLabel(Number(draft.stock) || 0) }}</span>
          </div>
          <p class="numeric font-display text-2xl font-extrabold text-strong">{{ previewPrice }}</p>
          <p class="text-sm leading-relaxed text-muted">
            {{ draft.description.trim() || 'Ajoutez une description pour la fiche boutique.' }}
          </p>
        </div>
      </div>
    </aside>
  </div>
</template>
