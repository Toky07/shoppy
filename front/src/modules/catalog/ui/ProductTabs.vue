<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  description: string | null
}>()

const tabs = [
  { id: 'description', label: 'Description' },
  { id: 'delivery', label: 'Livraison' },
  { id: 'warranty', label: 'Garantie' },
] as const

const activeTab = ref<(typeof tabs)[number]['id']>('description')
</script>

<template>
  <div class="mt-10">
    <div class="flex gap-1 border-b border-line" role="tablist" aria-label="Détails du produit">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        role="tab"
        class="relative -mb-px border-b-2 px-4 py-3 text-sm font-semibold transition-colors"
        :class="
          activeTab === tab.id
            ? 'border-accent text-strong'
            : 'border-transparent text-muted hover:text-strong'
        "
        :aria-selected="activeTab === tab.id"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="pt-6 text-sm leading-relaxed text-body">
      <template v-if="activeTab === 'description'">
        <p v-if="description">{{ description }}</p>
        <p v-else class="text-faint italic">Aucune description disponible pour ce produit.</p>
      </template>
      <p v-else-if="activeTab === 'delivery'">
        Expédition depuis la France sous 48 heures ouvrées, avec numéro de suivi. Livraison offerte
        à partir de 49 € d'achat, retours gratuits pendant 30 jours.
      </p>
      <p v-else>
        Deux ans de garantie constructeur sur les pièces et la main d'œuvre. En cas de souci, on
        remplace ou on rembourse, sans formulaire à rallonge.
      </p>
    </div>
  </div>
</template>
