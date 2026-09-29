<script setup lang="ts">
import { computed } from 'vue'
import type { Money } from '@/shared/money/Money'
import { formatMoney } from '@/shared/money/formatMoney'

const props = defineProps<{
  price: Money
}>()

const label = computed(() => formatMoney(props.price))
</script>

<template>
  <span>{{ label }}</span>
</template>
