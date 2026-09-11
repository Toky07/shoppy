<script setup lang="ts">
defineProps<{
  status: 'loading' | 'ready' | 'empty' | 'error'
  errorMessage?: string
}>()
</script>

<template>
  <!-- État de chargement -->
  <div v-if="status === 'loading'" class="flex flex-col items-center justify-center min-h-[60vh] w-full">
    <div class="relative flex items-center justify-center">
      <!-- Cercle extérieur statique -->
      <div class="absolute h-16 w-16 rounded-full border-4 border-gray-100"></div>
      <!-- Cercle intérieur animé -->
      <div class="absolute h-16 w-16 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
      <!-- Icône au centre -->
      <i class="fa-solid fa-store text-indigo-300 text-sm"></i>
    </div>
    <p class="mt-6 text-gray-500 font-medium animate-pulse tracking-wide uppercase text-sm">Chargement en cours...</p>
  </div>

  <!-- État d'erreur -->
  <div v-else-if="status === 'error'" role="alert" class="flex flex-col items-center justify-center py-16 px-6 text-center bg-red-50 rounded-3xl border border-red-100 my-8">
    <div class="h-16 w-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 shadow-sm">
      <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
    </div>
    <h3 class="text-lg font-bold text-red-900 mb-2">Oups ! Une erreur est survenue</h3>
    <p class="text-red-700 max-w-md font-medium">{{ errorMessage || 'Impossible de charger les données.' }}</p>
  </div>

  <!-- État vide -->
  <div v-else-if="status === 'empty'" class="w-full">
    <slot name="empty">
      <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm my-8">
        <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
          <i class="fa-solid fa-folder-open text-3xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium text-lg">Aucun contenu pour le moment.</p>
      </div>
    </slot>
  </div>

  <!-- État prêt (contenu) -->
  <slot v-else />
</template>
