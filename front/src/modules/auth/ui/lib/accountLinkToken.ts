import type { LocationQuery } from 'vue-router'

type AccountLinkRoute = {
  path: string
  query: LocationQuery
  hash: string
}

export function accountLinkToken(route: Pick<AccountLinkRoute, 'query' | 'hash'>): string {
  const fromHash = tokenFromHash(route.hash)
  if (fromHash !== '') {
    return fromHash
  }

  return typeof route.query.token === 'string' ? route.query.token : ''
}

export function exposesAccountToken(route: Pick<AccountLinkRoute, 'query' | 'hash'>): boolean {
  return tokenFromHash(route.hash) !== '' || typeof route.query.token === 'string'
}

export async function concealAccountLinkToken(
  router: { replace: (location: { path: string; query: LocationQuery; hash: string }) => Promise<unknown> },
  route: AccountLinkRoute,
): Promise<void> {
  if (!exposesAccountToken(route)) {
    return
  }

  const query = { ...route.query }
  delete query.token

  await router.replace({
    path: route.path,
    query,
    hash: '',
  })
}

function tokenFromHash(hash: string): string {
  const raw = hash.startsWith('#') ? hash.slice(1) : hash
  if (raw === '') {
    return ''
  }

  return new URLSearchParams(raw).get('token') ?? ''
}
