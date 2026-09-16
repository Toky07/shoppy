<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import AppIcon from './AppIcon.vue'

const visible = ref(false)

function onScroll() {
  visible.value = window.scrollY > 600
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
})

function toTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <Transition
    enter-from-class="opacity-0 translate-y-3"
    leave-to-class="opacity-0 translate-y-3"
    enter-active-class="transition duration-300"
    leave-active-class="transition duration-200"
  >
    <button
      v-if="visible"
      type="button"
      class="btn-outline fixed right-5 bottom-5 z-40 size-11 p-0 shadow-lifted backdrop-blur-md"
      aria-label="Revenir en haut"
      title="Revenir en haut"
      @click="toTop"
    >
      <AppIcon name="arrow-up" :size="17" />
    </button>
  </Transition>
</template>
