import type { RouterScrollBehavior } from 'vue-router'

export const scrollBehavior: RouterScrollBehavior = (to) => {
  if (to.hash) {
    return { el: to.hash }
  }

  return { top: 0, left: 0 }
}
