import { onBeforeUnmount, ref, type Ref } from 'vue'

const DRAG_THRESHOLD_PX = 6
const SETTLE_MS = 280

export type GalleryDragGhost = {
  key: string
  url: string
  width: number
  height: number
  x: number
  y: number
  lifted: boolean
  settling: boolean
}

type DraggableImage = {
  key: string
  url: string
}

export function useGalleryDrag(options: {
  images: Ref<DraggableImage[]>
  disabled: () => boolean
  reorder: (from: number, to: number) => void
  commit: (changed: boolean) => void
}) {
  const list = ref<HTMLElement | null>(null)
  const ghost = ref<GalleryDragGhost | null>(null)
  const activeKey = ref<string | null>(null)

  let pointerId: number | null = null
  let pendingKey: string | null = null
  let originIndex = -1
  let dragging = false
  let startX = 0
  let startY = 0
  let offsetX = 0
  let offsetY = 0
  let liftFrame = 0
  let settleTimer = 0
  let listRoot: HTMLElement | null = null

  function prefersReducedMotion() {
    return window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches === true
  }

  function indexOfKey(key: string) {
    return options.images.value.findIndex((image) => image.key === key)
  }

  function galleryRoot() {
    return listRoot ?? list.value
  }

  function indexFromPoint(x: number, y: number) {
    const root = galleryRoot()
    if (root === null) {
      return null
    }

    const items = [...root.querySelectorAll<HTMLElement>('[data-gallery-item]')]
    if (items.length === 0) {
      return null
    }

    let best = 0
    let bestDist = Number.POSITIVE_INFINITY
    for (const [index, item] of items.entries()) {
      const rect = item.getBoundingClientRect()
      const dx = rect.left + rect.width / 2 - x
      const dy = rect.top + rect.height / 2 - y
      const dist = dx * dx + dy * dy
      if (dist < bestDist) {
        bestDist = dist
        best = index
      }
    }

    return best
  }

  function holeRect(key: string) {
    const item = galleryRoot()?.querySelector(`[data-gallery-item="${cssEscape(key)}"]`)
    return item instanceof HTMLElement ? item.getBoundingClientRect() : null
  }

  function stopListening() {
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerup', onPointerUp)
    window.removeEventListener('pointercancel', onPointerUp)
  }

  function clearDragClass() {
    document.documentElement.classList.remove('gallery-dragging')
  }

  function resetPointer() {
    pointerId = null
    pendingKey = null
    dragging = false
    originIndex = -1
    listRoot = null
  }

  function finishGhost() {
    window.clearTimeout(settleTimer)
    if (liftFrame !== 0) {
      cancelAnimationFrame(liftFrame)
      liftFrame = 0
    }
    ghost.value = null
    activeKey.value = null
    clearDragClass()
    resetPointer()
  }

  function onPointerMove(event: PointerEvent) {
    if (event.pointerId !== pointerId) {
      return
    }

    const key = pendingKey ?? activeKey.value
    if (!dragging) {
      const dx = event.clientX - startX
      const dy = event.clientY - startY
      if (dx * dx + dy * dy < DRAG_THRESHOLD_PX * DRAG_THRESHOLD_PX) {
        return
      }

      const image = options.images.value[originIndex]
      const rect = key === null ? null : holeRect(key)
      if (!image || rect === null || key === null) {
        return
      }

      dragging = true
      activeKey.value = key
      document.documentElement.classList.add('gallery-dragging')
      ghost.value = {
        key,
        url: image.url,
        width: rect.width,
        height: rect.height,
        x: rect.left,
        y: rect.top,
        lifted: false,
        settling: false,
      }
      liftFrame = requestAnimationFrame(() => {
        if (ghost.value !== null) {
          ghost.value = { ...ghost.value, lifted: true }
        }
      })
    }

    event.preventDefault()
    if (ghost.value === null || key === null) {
      return
    }

    ghost.value = {
      ...ghost.value,
      x: event.clientX - offsetX,
      y: event.clientY - offsetY,
    }

    const from = indexOfKey(key)
    const to = indexFromPoint(event.clientX, event.clientY)
    if (from < 0 || to === null || from === to) {
      return
    }

    options.reorder(from, to)
  }

  function onPointerUp(event: PointerEvent) {
    if (event.pointerId !== pointerId) {
      return
    }

    stopListening()

    if (!dragging || activeKey.value === null || ghost.value === null) {
      finishGhost()
      return
    }

    const key = activeKey.value
    const changed = indexOfKey(key) !== originIndex
    options.commit(changed)
    clearDragClass()

    const settle = !prefersReducedMotion()
    const target = holeRect(key)
    if (!settle || target === null) {
      finishGhost()
      return
    }

    ghost.value = {
      ...ghost.value,
      x: target.left,
      y: target.top,
      lifted: false,
      settling: true,
    }
    pointerId = null
    dragging = false
    pendingKey = null
    settleTimer = window.setTimeout(() => {
      finishGhost()
    }, SETTLE_MS)
  }

  function onItemPointerDown(event: PointerEvent, key: string) {
    if (options.disabled() || event.button !== 0 || options.images.value.length < 2) {
      return
    }

    const origin = event.target
    if (origin instanceof Element && origin.closest('[data-gallery-no-drag]')) {
      return
    }

    const item = event.currentTarget
    if (!(item instanceof HTMLElement)) {
      return
    }

    window.clearTimeout(settleTimer)
    ghost.value = null
    activeKey.value = null
    const rect = item.getBoundingClientRect()
    pointerId = event.pointerId
    pendingKey = key
    originIndex = indexOfKey(key)
    startX = event.clientX
    startY = event.clientY
    offsetX = event.clientX - rect.left
    offsetY = event.clientY - rect.top
    listRoot = item.closest('[data-gallery-list]') ?? item.parentElement
    if (event.cancelable) {
      event.preventDefault()
    }
    try {
      item.setPointerCapture?.(event.pointerId)
    } catch {
      // happy-dom and some browsers reject capture on detached nodes
    }
    window.addEventListener('pointermove', onPointerMove)
    window.addEventListener('pointerup', onPointerUp)
    window.addEventListener('pointercancel', onPointerUp)
  }

  onBeforeUnmount(() => {
    stopListening()
    finishGhost()
  })

  return {
    list,
    ghost,
    activeKey,
    onItemPointerDown,
  }
}

function cssEscape(value: string) {
  if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
    return CSS.escape(value)
  }

  return value.replace(/"/g, '\\"')
}
