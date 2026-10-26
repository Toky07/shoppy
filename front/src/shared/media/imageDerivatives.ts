export type ImageVariant = 'thumb' | 'card' | 'detail'

const WIDTHS: Record<ImageVariant, number> = {
  thumb: 128,
  card: 600,
  detail: 1200,
}

const SIZES: Record<ImageVariant, string> = {
  thumb: '96px',
  card: '(max-width: 640px) 50vw, 280px',
  detail: '(min-width: 1024px) 640px, 100vw',
}

const VARIANTS = Object.keys(WIDTHS) as ImageVariant[]

export function isRasterUpload(src: string): boolean {
  if (!src.startsWith('/uploads/')) {
    return false
  }

  return !src.toLowerCase().endsWith('.svg') && !src.toLowerCase().endsWith('.gif')
}

export function derivativeUrl(src: string, variant: ImageVariant): string {
  const dot = src.lastIndexOf('.')
  if (dot === -1) {
    return src
  }

  return `${src.slice(0, dot)}.${variant}${src.slice(dot)}`
}

export function imageSrc(src: string, variant: ImageVariant): string {
  return isRasterUpload(src) ? derivativeUrl(src, variant) : src
}

export function imageSrcSet(src: string): string | undefined {
  if (!isRasterUpload(src)) {
    return undefined
  }

  return VARIANTS.map((variant) => `${derivativeUrl(src, variant)} ${WIDTHS[variant]}w`).join(', ')
}

export function imageSizes(variant: ImageVariant): string | undefined {
  return SIZES[variant]
}
