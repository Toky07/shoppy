import { onBeforeUnmount, ref, watch } from 'vue'

export function usePopupMenu() {
  const open = ref(false)
  const triggerEl = ref<HTMLButtonElement>()
  const menuEl = ref<HTMLElement>()

  function items() {
    return [...(menuEl.value?.querySelectorAll<HTMLElement>('[role="menuitem"]') ?? [])]
  }

  function focusItem(index: number) {
    const all = items()

    if (all.length === 0) {
      return
    }

    all[(index + all.length) % all.length]?.focus()
  }

  function close(refocus = false) {
    open.value = false

    if (refocus) {
      triggerEl.value?.focus()
    }
  }

  async function openAt(edge: 'first' | 'last') {
    open.value = true
    await new Promise(requestAnimationFrame)
    focusItem(edge === 'first' ? 0 : items().length - 1)
  }

  function onTriggerKeydown(event: KeyboardEvent) {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      void openAt('first')
    }

    if (event.key === 'ArrowUp') {
      event.preventDefault()
      void openAt('last')
    }
  }

  function onMenuKeydown(event: KeyboardEvent) {
    const all = items()
    const current = all.indexOf(document.activeElement as HTMLElement)

    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault()
        focusItem(current + 1)
        break
      case 'ArrowUp':
        event.preventDefault()
        focusItem(current - 1)
        break
      case 'Home':
        event.preventDefault()
        focusItem(0)
        break
      case 'End':
        event.preventDefault()
        focusItem(all.length - 1)
        break
      case 'Tab':
        close()
        break
    }
  }

  function onDocumentKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      close(true)
    }
  }

  function onDocumentPointerDown(event: PointerEvent) {
    const target = event.target as Node

    if (!triggerEl.value?.contains(target) && !menuEl.value?.contains(target)) {
      close()
    }
  }

  watch(open, (isOpen) => {
    if (isOpen) {
      document.addEventListener('keydown', onDocumentKeydown)
      document.addEventListener('pointerdown', onDocumentPointerDown)
      return
    }

    document.removeEventListener('keydown', onDocumentKeydown)
    document.removeEventListener('pointerdown', onDocumentPointerDown)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('keydown', onDocumentKeydown)
    document.removeEventListener('pointerdown', onDocumentPointerDown)
  })

  return {
    open,
    triggerEl,
    menuEl,
    close,
    onTriggerKeydown,
    onMenuKeydown,
  }
}
