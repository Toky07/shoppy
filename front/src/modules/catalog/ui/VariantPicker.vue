<script setup lang="ts">
import type { ProductVariant } from '../domain/Product'

const props = defineProps<{
  variants: ProductVariant[]
}>()

const selectedId = defineModel<string>({ required: true })

function unique(values: Array<string | null>): string[] {
  return [...new Set(values.filter((value): value is string => value !== null))]
}

function selected(): ProductVariant | undefined {
  return props.variants.find((variant) => variant.id === selectedId.value) ?? props.variants[0]
}

function choose(size: string | null, color: string | null) {
  const match =
    props.variants.find((variant) => variant.size === size && variant.color === color) ??
    props.variants.find((variant) => (size === null || variant.size === size) && (color === null || variant.color === color))

  if (match) {
    selectedId.value = match.id
  }
}
</script>

<template>
  <div class="mt-8 flex flex-col gap-5">
    <div v-if="unique(variants.map((variant) => variant.color)).length > 0">
      <p class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">Couleur</p>
      <div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="Couleur">
        <button
          v-for="color in unique(variants.map((variant) => variant.color))"
          :key="color"
          type="button"
          role="radio"
          class="rounded-full border px-3 py-1.5 text-sm font-medium transition-colors"
          :class="
            selected()?.color === color
              ? 'border-line-strong bg-primary text-primary-fg'
              : 'border-line bg-surface-inset text-muted hover:text-strong'
          "
          :aria-checked="selected()?.color === color"
          @click="choose(selected()?.size ?? null, color)"
        >
          {{ color }}
        </button>
      </div>
    </div>

    <div v-if="unique(variants.map((variant) => variant.size)).length > 0">
      <p class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">Taille</p>
      <div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="Taille">
        <button
          v-for="size in unique(variants.map((variant) => variant.size))"
          :key="size"
          type="button"
          role="radio"
          class="rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors"
          :class="
            selected()?.size === size
              ? 'border-line-strong bg-primary text-primary-fg'
              : 'border-line bg-surface-inset text-muted hover:text-strong'
          "
          :aria-checked="selected()?.size === size"
          @click="choose(size, selected()?.color ?? null)"
        >
          {{ size }}
        </button>
      </div>
    </div>
  </div>
</template>
