import type { HttpQuery } from './HttpClient'

export function withQuery(path: string, query?: HttpQuery): string {
  if (!query) {
    return path
  }

  const params = new URLSearchParams()

  for (const [key, value] of Object.entries(query)) {
    if (value === undefined) {
      continue
    }

    params.set(key, String(value))
  }

  const search = params.toString()

  return search === '' ? path : `${path}?${search}`
}
