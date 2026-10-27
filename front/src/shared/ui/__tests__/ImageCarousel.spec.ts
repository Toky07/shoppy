import { fireEvent, render, screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { localeProvide } from '@/shared/testing/localeProvide'
import ImageCarousel from '../ImageCarousel.vue'

const images = ['/uploads/one.jpg', '/uploads/two.jpg', '/uploads/three.jpg']

function carouselImg(name: string) {
  return waitFor(() => {
    const node = screen.getByRole('img', { name })
    expect(node.tagName).toBe('IMG')
    return node
  })
}

describe('ImageCarousel', () => {
  it('shows a single image without controls', async () => {
    render(ImageCarousel, {
      props: { images: ['/uploads/one.jpg'], alt: 'Nuvora Tee' },
      global: { provide: localeProvide() },
    })

    expect((await carouselImg('Nuvora Tee')).getAttribute('src')).toBe('/uploads/one.card.jpg')
    expect(screen.queryByRole('button', { name: 'Image suivante' })).toBeNull()
  })

  it('browses the gallery with next and previous', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee' },
      global: { provide: localeProvide() },
    })

    expect((await carouselImg('Nuvora Tee (1/3)')).getAttribute('src')).toBe('/uploads/one.card.jpg')

    await userEvent.click(screen.getByRole('button', { name: 'Image suivante' }))
    expect((await carouselImg('Nuvora Tee (2/3)')).getAttribute('src')).toBe('/uploads/two.card.jpg')

    await userEvent.click(screen.getByRole('button', { name: 'Image précédente' }))
    expect((await carouselImg('Nuvora Tee (1/3)')).getAttribute('src')).toBe('/uploads/one.card.jpg')
  })

  it('changes slide on a horizontal swipe', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee', interactive: true },
      global: { provide: localeProvide() },
    })

    await carouselImg('Nuvora Tee (1/3)')
    const track = screen.getByRole('group', { name: 'Nuvora Tee' }).firstElementChild as HTMLElement

    await fireEvent.pointerDown(track, { pointerId: 1, pointerType: 'touch', clientX: 200, clientY: 40 })
    await fireEvent.pointerMove(track, { pointerId: 1, pointerType: 'touch', clientX: 120, clientY: 42 })
    await fireEvent.pointerUp(track, { pointerId: 1, pointerType: 'touch', clientX: 80, clientY: 44 })

    expect((await carouselImg('Nuvora Tee (2/3)')).getAttribute('src')).toBe('/uploads/two.card.jpg')
  })

  it('ignores a vertical swipe so the page can still scroll', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee', interactive: true },
      global: { provide: localeProvide() },
    })

    await carouselImg('Nuvora Tee (1/3)')
    const track = screen.getByRole('group', { name: 'Nuvora Tee' }).firstElementChild as HTMLElement

    await fireEvent.pointerDown(track, { pointerId: 1, pointerType: 'touch', clientX: 120, clientY: 40 })
    await fireEvent.pointerMove(track, { pointerId: 1, pointerType: 'touch', clientX: 118, clientY: 140 })
    await fireEvent.pointerUp(track, { pointerId: 1, pointerType: 'touch', clientX: 116, clientY: 200 })

    expect((await carouselImg('Nuvora Tee (1/3)')).getAttribute('src')).toBe('/uploads/one.card.jpg')
  })

  it('jumps to a slide from a thumbnail control', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee' },
      global: { provide: localeProvide() },
    })

    await carouselImg('Nuvora Tee (1/3)')
    await userEvent.click(screen.getByRole('tab', { name: "Aller à l'image 3" }))
    expect((await carouselImg('Nuvora Tee (3/3)')).getAttribute('src')).toBe('/uploads/three.card.jpg')
  })
})
