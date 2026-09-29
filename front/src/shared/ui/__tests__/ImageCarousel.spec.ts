import { render, screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
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
    })

    expect((await carouselImg('Nuvora Tee')).getAttribute('src')).toBe('/uploads/one.jpg')
    expect(screen.queryByRole('button', { name: 'Image suivante' })).toBeNull()
  })

  it('browses the gallery with next and previous', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee' },
    })

    expect((await carouselImg('Nuvora Tee (1/3)')).getAttribute('src')).toBe('/uploads/one.jpg')

    await userEvent.click(screen.getByRole('button', { name: 'Image suivante' }))
    expect((await carouselImg('Nuvora Tee (2/3)')).getAttribute('src')).toBe('/uploads/two.jpg')

    await userEvent.click(screen.getByRole('button', { name: 'Image précédente' }))
    expect((await carouselImg('Nuvora Tee (1/3)')).getAttribute('src')).toBe('/uploads/one.jpg')
  })

  it('jumps to a slide from a thumbnail control', async () => {
    render(ImageCarousel, {
      props: { images, alt: 'Nuvora Tee' },
    })

    await carouselImg('Nuvora Tee (1/3)')
    await userEvent.click(screen.getByRole('tab', { name: "Aller à l'image 3" }))
    expect((await carouselImg('Nuvora Tee (3/3)')).getAttribute('src')).toBe('/uploads/three.jpg')
  })
})
