import { safeRedirectPath } from '@/shared/routing/safeRedirectPath'

export function adminRedirectPath(value: unknown): string {
  const path = safeRedirectPath(value)

  if (path.startsWith('/admin') && path !== '/admin/login' && !path.startsWith('/admin/login?')) {
    return path
  }

  return '/admin'
}
