<script setup lang="ts">
import { computed } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import ImageCarousel from '@/shared/ui/ImageCarousel.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { formatMoney } from '@/shared/money/formatMoney'
import { stockLabel } from '@/modules/catalog/ui/stockLabel'
import type { ProductDraft } from './productDraft'

const props = defineProps<{
  isCreate: boolean
  pending: boolean
  errorMessage?: string
  previewImages?: string[]
  slug?: string | null
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
                class="field numeric mt-3 border-transparent bg-surface text-xl font-extrabold"
              />
            </label>
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
