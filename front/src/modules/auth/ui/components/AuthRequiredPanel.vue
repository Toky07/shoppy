<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'

const props = defineProps<{
  message: string
  redirect: string
  title?: string
  actionLabel?: string
}>()

const { t } = useI18n()
const resolvedTitle = computed(() => props.title ?? t('auth.requiredTitle'))
const resolvedAction = computed(() => props.actionLabel ?? t('auth.requiredAction'))
</script>

<template>
  <div class="panel mx-auto mt-12 max-w-md p-8 text-center">
    <div
      class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
    >
      <AppIcon name="lock" :size="24" />
    </div>
    <h2 class="text-xl font-bold text-strong">{{ resolvedTitle }}</h2>
    <p class="mt-3 text-sm text-muted">{{ message }}</p>
    <RouterLink
      :to="{ path: '/login', query: { redirect } }"
      class="btn-primary btn-lg mt-8 w-full"
    >
      {{ resolvedAction }}
    </RouterLink>
  </div>
</template>
