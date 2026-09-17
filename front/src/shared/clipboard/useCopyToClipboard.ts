import { onBeforeUnmount, ref } from 'vue'

export function useCopyToClipboard(resetMs = 2000) {
  const copied = ref(false)
  let generation = 0

  async function copy(value: string) {
    const current = ++generation

    try {
      await navigator.clipboard.writeText(value)
      copied.value = true
      window.setTimeout(() => {
        if (current === generation) {
          copied.value = false
        }
      }, resetMs)
    } catch {
      /* presse-papiers indisponible */
    }
  }

  onBeforeUnmount(() => {
    generation += 1
  })

  return { copied, copy }
}
