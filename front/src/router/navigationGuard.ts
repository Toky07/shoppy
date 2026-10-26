import type { NavigationGuardWithThis } from 'vue-router'

export type RouteAuthState = {
  isAuthenticated: { readonly value: boolean }
  isAdmin: { readonly value: boolean }
}

export function createNavigationGuard(session: RouteAuthState): NavigationGuardWithThis<undefined> {
  return (to) => {
    const requiresAdmin = to.matched.some((record) => record.meta.requiresAdmin === true)
    const requiresAuth =
      requiresAdmin || to.matched.some((record) => record.meta.requiresAuth === true)

    if (!requiresAuth || session.isAuthenticated.value) {
      return true
    }

    if (requiresAdmin) {
      return { name: 'admin-login', query: { redirect: to.fullPath } }
    }

    return { path: '/login', query: { redirect: to.fullPath } }
  }
}
