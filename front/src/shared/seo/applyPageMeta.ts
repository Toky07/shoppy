export const DEFAULT_PAGE_TITLE = 'Shoppy — Boutique'
export const DEFAULT_PAGE_DESCRIPTION =
  "Shoppy — une sélection d'objets utiles, livrés vite et payés en toute sécurité."

export type PageMeta = {
  title: string
  description: string
  image?: string | null
  path?: string
  type?: 'website' | 'product'
}

function upsertMeta(attribute: 'name' | 'property', key: string, content: string): void {
  const selector = `meta[${attribute}="${key}"]`
  let element = document.head.querySelector(selector)

  if (!(element instanceof HTMLMetaElement)) {
    element = document.createElement('meta')
    element.setAttribute(attribute, key)
    document.head.appendChild(element)
  }

  element.setAttribute('content', content)
}

function upsertLink(rel: string, href: string): void {
  const existing = document.head.querySelector(`link[rel="${rel}"]`)
  const element = existing instanceof HTMLLinkElement ? existing : document.createElement('link')

  if (element !== existing) {
    element.rel = rel
    document.head.appendChild(element)
  }

  element.href = href
}

function absoluteUrl(path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }

  return new URL(path, window.location.origin).href
}

export function applyPageMeta(meta: PageMeta): void {
  const type = meta.type ?? 'website'
  const url = meta.path === undefined ? window.location.href : absoluteUrl(meta.path)

  document.title = meta.title
  upsertMeta('name', 'description', meta.description)
  upsertMeta('property', 'og:title', meta.title)
  upsertMeta('property', 'og:description', meta.description)
  upsertMeta('property', 'og:type', type)
  upsertMeta('property', 'og:url', url)
  upsertMeta('name', 'twitter:card', meta.image ? 'summary_large_image' : 'summary')
  upsertLink('canonical', url)

  if (meta.image) {
    upsertMeta('property', 'og:image', absoluteUrl(meta.image))
  } else {
    document.head.querySelector('meta[property="og:image"]')?.remove()
  }
}

export function resetPageMeta(): void {
  applyPageMeta({
    title: DEFAULT_PAGE_TITLE,
    description: DEFAULT_PAGE_DESCRIPTION,
    path: '/',
    type: 'website',
  })
}
