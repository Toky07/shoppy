import { render, screen } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import AppImage from '../AppImage.vue'

describe('AppImage', () => {
  it('lazy-loads an image', () => {
    render(AppImage, { props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee' } })

    const image = screen.getByRole('img', { name: 'Nuvora Tee' })
    expect(image.getAttribute('src')).toBe('/uploads/tee.jpg')
    expect(image.getAttribute('loading')).toBe('lazy')
    expect(image.getAttribute('decoding')).toBe('async')
  })

  it('can load the first image eagerly', () => {
    render(AppImage, {
      props: { src: '/uploads/tee.jpg', alt: 'Nuvora Tee', loading: 'eager' },
    })

    expect(screen.getByRole('img', { name: 'Nuvora Tee' }).getAttribute('loading')).toBe('eager')
  })

  it('renders a placeholder when there is no source', () => {
    render(AppImage, { props: { src: null, alt: 'Nuvora Mug' } })

    expect(screen.queryByRole('img', { name: 'Nuvora Mug' })?.tagName).not.toBe('IMG')
    expect(screen.getByRole('img', { name: 'Nuvora Mug' })).toBeTruthy()
  })
})
