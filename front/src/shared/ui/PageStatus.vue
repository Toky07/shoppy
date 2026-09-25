<script setup lang="ts">
import AppIcon from './AppIcon.vue'
import EmptyState from './EmptyState.vue'

withDefaults(
  defineProps<{
    status: 'loading' | 'ready' | 'empty' | 'error'
    errorMessage?: string
    skeleton?: 'spinner' | 'cards' | 'rows' | 'detail'
  }>(),
  { skeleton: 'spinner' },
)
</script>

<template>
  <template v-if="status === 'loading'">
    <p v-if="skeleton !== 'spinner'" class="sr-only">Chargement en cours...</p>

    <div v-if="skeleton === 'cards'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="index in 8" :key="index" class="panel-flat overflow-hidden">
        <div class="skeleton aspect-4/5 rounded-none"></div>
        <div class="space-y-3 p-5">
          <div class="skeleton h-4 w-3/4"></div>
          <div class="skeleton h-3 w-1/3"></div>
        </div>
      </div>
    </div>

    <div v-else-if="skeleton === 'rows'" class="space-y-3">
      <div v-for="index in 5" :key="index" class="panel-flat flex items-center gap-4 p-5">
        <div class="skeleton size-14 rounded-2xl"></div>
        <div class="flex-1 space-y-3">
          <div class="skeleton h-4 w-2/5"></div>
          <div class="skeleton h-3 w-1/4"></div>
        </div>
        <div class="skeleton h-8 w-24 rounded-full"></div>
      </div>
    </div>

    <div v-else-if="skeleton === 'detail'" class="grid gap-10 lg:grid-cols-2">
      <div class="skeleton aspect-4/5 rounded-panel"></div>
      <div class="space-y-5 py-4">
        <div class="skeleton h-5 w-28 rounded-full"></div>
        <div class="skeleton h-10 w-4/5"></div>
        <div class="skeleton h-8 w-32"></div>
        <div class="skeleton h-24 w-full"></div>
        <div class="skeleton h-14 w-full rounded-full"></div>
      </div>
    </div>

    <div v-else class="flex min-h-[50vh] flex-col items-center justify-center gap-5">
      <span class="text-strong animate-orbit">
        <AppIcon name="loader" :size="30" :stroke-width="2" />
      </span>
      <p class="text-xs font-semibold tracking-[0.2em] text-muted uppercase">Chargement en cours...</p>
    </div>
  </template>

  <!-- Erreur -->
  <div
    v-else-if="status === 'error'"
    role="alert"
    class="notice-danger my-6 flex-col items-center px-6 py-14 text-center sm:flex-row sm:items-start sm:text-left"
  >
    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-danger/10">
      <AppIcon name="alert-triangle" :size="22" />
    </span>
    <span class="sm:pt-2">
      <strong class="block font-display text-base">Une erreur est survenue</strong>
      <span class="mt-1 block text-sm opacity-90">{{
        errorMessage || 'Impossible de charger les données.'
      }}</span>
    </span>
  </div>

  <!-- Vide -->
  <div v-else-if="status === 'empty'" class="w-full">
    <slot name="empty">
      <EmptyState
        icon="package"
        title="Rien à afficher"
        description="Ce contenu est vide pour le moment."
      />
    </slot>
  </div>

  <slot v-else />
</template>
