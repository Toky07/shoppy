<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import type { Money } from '@/shared/money/Money'
import { formatMoney } from '@/shared/money/formatMoney'

const props = defineProps<{
  price: Money
}>()

const { intlLocale } = useI18n()
const label = computed(() => formatMoney(props.price, intlLocale.value))
</script>

<template>
  <span>{{ label }}</span>
</template>
