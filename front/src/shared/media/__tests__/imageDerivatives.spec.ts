import { describe, expect, it } from 'vitest'
import { derivativeUrl, imageSizes, imageSrc, imageSrcSet, isRasterUpload } from '../imageDerivatives'

describe('imageDerivatives', () => {
  it('builds raster upload derivative urls and a srcset', () => {
    const src = '/uploads/2026/09/tee.jpg'

    expect(isRasterUpload(src)).toBe(true)
    expect(imageSrc(src, 'card')).toBe('/uploads/2026/09/tee.card.jpg')
    expect(derivativeUrl(src, 'thumb')).toBe('/uploads/2026/09/tee.thumb.jpg')
    expect(imageSrcSet(src)).toBe(
      '/uploads/2026/09/tee.thumb.jpg 128w, /uploads/2026/09/tee.card.jpg 600w, /uploads/2026/09/tee.detail.jpg 1200w',
    )
    expect(imageSizes('thumb')).toBe('96px')
  })

  it('leaves svg and non-upload sources unchanged', () => {
    expect(imageSrc('/media/products/nuvora-tee.svg', 'card')).toBe('/media/products/nuvora-tee.svg')
    expect(imageSrcSet('/media/products/nuvora-tee.svg')).toBeUndefined()
    expect(imageSrcSet('https://cdn.shoppy.test/tee.jpg')).toBeUndefined()
  })
})
