import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapProduct, mapProductPage } from '../productMapper'
import { createProductJson, nuvoraTee } from '../../testing/productFixtures'

describe('mapProduct', () => {
  it('maps a product JSON payload', () => {
    expect(mapProduct(createProductJson())).toEqual(nuvoraTee)
  })

  it('maps a null description', () => {
    expect(mapProduct(createProductJson({ description: null })).description).toBeNull()
  })

  it('maps a null image', () => {
    expect(mapProduct(createProductJson({ imageUrl: null, imageUrls: [] }))).toMatchObject({
      imageUrl: null,
      imageUrls: [],
    })
  })

  it('maps a gallery of image urls', () => {
    expect(
      mapProduct(
        createProductJson({
          imageUrl: '/uploads/one.jpg',
          imageUrls: ['/uploads/one.jpg', '/uploads/two.jpg'],
        }),
      ),
    ).toMatchObject({
      imageUrl: '/uploads/one.jpg',
      imageUrls: ['/uploads/one.jpg', '/uploads/two.jpg'],
    })
  })

  it('falls back to a single imageUrl when imageUrls is omitted', () => {
    const { imageUrls: _imageUrls, ...payload } = createProductJson()

    expect(mapProduct(payload).imageUrls).toEqual([nuvoraTee.imageUrl])
  })

  it('rejects an invalid payload', () => {
    expect(() => mapProduct({ id: 'x' })).toThrow(InvalidResponseError)
  })
})

describe('mapProductPage', () => {
  it('maps a paginated list', () => {
    expect(
      mapProductPage({
        items: [createProductJson()],
        page: 1,
        limit: 20,
        total: 1,
      }),
    ).toEqual({
      items: [nuvoraTee],
      page: 1,
      limit: 20,
      total: 1,
    })
  })

  it('rejects an invalid list payload', () => {
    expect(() => mapProductPage({ items: [] })).toThrow(InvalidResponseError)
  })
})
