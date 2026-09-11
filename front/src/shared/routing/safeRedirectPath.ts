export function safeRedirectPath(value: unknown): string {
  const raw = Array.isArray(value) ? value[0] : value

  if (typeof raw === 'string' && raw.startsWith('/') && !raw.startsWith('//')) {
    return raw
  }

  return '/'
}
