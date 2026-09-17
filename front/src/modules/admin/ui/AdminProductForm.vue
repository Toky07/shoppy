<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import type { ProductDraft } from './productDraft'

defineProps<{
  isCreate: boolean
  pending: boolean
  errorMessage?: string
}>()

const draft = defineModel<ProductDraft>({ required: true })

const emit = defineEmits<{
  submit: []
  delete: []
}>()
</script>

<template>
  <form class="panel mt-10 max-w-xl space-y-6 p-6 sm:p-8" @submit.prevent="emit('submit')">
    <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>

    <label class="block">
      <span class="field-label">Nom</span>
      <input v-model="draft.name" required class="field" placeholder="Nuvora Tee" />
    </label>

    <div class="grid gap-6 sm:grid-cols-2">
      <label class="block">
        <span class="field-label">Prix (€)</span>
        <input
          v-model.number="draft.priceEuros"
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
          v-model.number="draft.stock"
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
        v-model="draft.description"
        rows="4"
        class="field resize-y"
        placeholder="Ce qui rend ce produit utile, en deux phrases."
      />
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
        @click="emit('delete')"
      >
        <AppIcon name="trash" :size="15" />
        Supprimer
      </button>
    </div>
  </form>
</template>
