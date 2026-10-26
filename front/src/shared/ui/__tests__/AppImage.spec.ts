import { fireEvent, render, screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import AppImage from '../AppImage.vue'

describe('AppImage', () => {
  it('lazy-loads an image when fetching is allowed', async () => {
    render(AppImage, { props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee' } })

    const image = await waitFor(() => {
      const node = screen.getByRole('img', { name: 'Nuvora Tee' })
      expect(node.tagName).toBe('IMG')
      return node
    })

    expect(image.getAttribute('src')).toBe('/uploads/tee.card.jpg')
    expect(image.getAttribute('srcset')).toBe(
      '/uploads/tee.thumb.jpg 128w, /uploads/tee.card.jpg 600w, /uploads/tee.detail.jpg 1200w',
    )
    expect(image.getAttribute('sizes')).toBe('(max-width: 640px) 50vw, 280px')
    expect(image.getAttribute('loading')).toBe('lazy')
    expect(image.getAttribute('decoding')).toBe('async')
  })

  it('uses a thumb derivative for compact slots', async () => {
    render(AppImage, { props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee', variant: 'thumb' } })

    const image = await waitFor(() => {
      const node = screen.getByRole('img', { name: 'Nuvora Tee' })
      expect(node.tagName).toBe('IMG')
      return node
    })

    expect(image.getAttribute('src')).toBe('/uploads/tee.thumb.jpg')
    expect(image.getAttribute('sizes')).toBe('96px')
  })

  it('falls back to the original url when a derivative is missing', async () => {
    render(AppImage, { props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee' } })

    const image = await waitFor(() => {
      const node = screen.getByRole('img', { name: 'Nuvora Tee' })
      expect(node.tagName).toBe('IMG')
      return node
    })
    await fireEvent.error(image)

    expect(image.getAttribute('src')).toBe('/uploads/tee.jpg')
    expect(image.getAttribute('srcset')).toBeNull()
  })

  it('does not request the image until fetching is enabled', () => {
    render(AppImage, {
      props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee', fetch: false, viewport: false },
    })

    expect(screen.queryByRole('img', { name: 'Nuvora Tee' })?.tagName).not.toBe('IMG')
    expect(screen.getByRole('img', { name: 'Nuvora Tee' })).toBeTruthy()
  })

  it('renders a placeholder when there is no source', () => {
    render(AppImage, { props: { src: null, alt: 'Nuvora Mug' } })

    expect(screen.queryByRole('img', { name: 'Nuvora Mug' })?.tagName).not.toBe('IMG')
    expect(screen.getByRole('img', { name: 'Nuvora Mug' })).toBeTruthy()
  })
})
