import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue'

type UseInViewportOptions = {
  rootMargin?: string
  threshold?: number
}

export function useInViewport(
  target: Ref<HTMLElement | null>,
  options: UseInViewportOptions = {},
): Ref<boolean> {
  const inViewport = ref(false)
  let observer: IntersectionObserver | null = null

  onMounted(() => {
    const element = target.value
    if (element === null) {
      return
    }

    if (typeof IntersectionObserver === 'undefined') {
      inViewport.value = true
      return
    }

    observer = new IntersectionObserver(
      (entries) => {
        inViewport.value = entries.some((entry) => entry.isIntersecting)
      },
      {
        rootMargin: options.rootMargin ?? '0px',
        threshold: options.threshold ?? 0.01,
      },
    )

    observer.observe(element)
  })

  onBeforeUnmount(() => {
    observer?.disconnect()
  })

  return inViewport
}
