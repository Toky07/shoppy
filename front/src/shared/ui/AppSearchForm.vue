<script setup lang="ts">
import AppIcon from './AppIcon.vue'

defineProps<{
  id: string
  label: string
  placeholder: string
}>()

const model = defineModel<string>({ required: true })
const emit = defineEmits<{
  submit: []
}>()
</script>

<template>
  <form role="search" @submit.prevent="emit('submit')">
    <label class="sr-only" :for="id">{{ label }}</label>
    <div class="relative">
      <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
        <AppIcon name="search" :size="16" />
      </span>
      <input
        :id="id"
        v-model="model"
        type="search"
        :placeholder="placeholder"
        class="field rounded-full bg-surface-inset py-2.5 pl-11 text-sm"
      />
    </div>
  </form>
</template>
