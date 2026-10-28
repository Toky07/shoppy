import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { localeProvide } from '@/shared/testing/localeProvide'
import { moveItem } from '../../lib/moveItem'
import AdminProductGallery, { type GalleryImage } from '../AdminProductGallery.vue'

const galleryImages: GalleryImage[] = [
  { key: 'a', url: '/uploads/2026/09/front.jpg', id: 'media-1' },
  { key: 'b', url: '/uploads/2026/09/back.jpg', id: 'media-2' },
]

function mockGalleryRects() {
  vi.spyOn(HTMLElement.prototype, 'getBoundingClientRect').mockImplementation(function (this: HTMLElement) {
    const key = this.getAttribute('data-gallery-item')
    if (key === null) {
      return new DOMRect(0, 0, 0, 0)
    }
    const index = key === 'b' ? 1 : 0
    return new DOMRect(index * 220, 0, 200, 240)
  })
}

function dispatchPointer(target: EventTarget, type: string, clientX: number, clientY: number) {
  target.dispatchEvent(
    new PointerEvent(type, {
      bubbles: true,
      cancelable: true,
      pointerId: 1,
      button: 0,
      clientX,
      clientY,
    }),
  )
}

describe('AdminProductGallery', () => {
  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('reorders images by dragging a card onto another', async () => {
    mockGalleryRects()
    let images = galleryImages.map((image) => ({ ...image }))
    const view = render(AdminProductGallery, {
      props: { images, pending: false, queued: false },
      global: { provide: localeProvide() },
    })

    const first = screen.getByRole('listitem', { name: "Glisser l'image 1" })
    dispatchPointer(first, 'pointerdown', 20, 20)
    dispatchPointer(window, 'pointermove', 40, 20)
    dispatchPointer(window, 'pointermove', 280, 40)

    expect(view.emitted('reorder')).toEqual([[0, 1]])

    const reorder = view.emitted('reorder')?.[0] as [number, number]
    images = moveItem(images, reorder[0], reorder[1])
    await view.rerender({ images, pending: false, queued: false })

    dispatchPointer(window, 'pointerup', 280, 40)

    expect(view.emitted('reorder-end')).toEqual([[true]])
  })

  it('moves an image with the arrow buttons', async () => {
    const { emitted } = render(AdminProductGallery, {
      props: { images: galleryImages, pending: false, queued: false },
      global: { provide: localeProvide() },
    })

    await userEvent.click(screen.getByRole('button', { name: "Reculer l'image 1" }))

    expect(emitted().move).toEqual([[0, 1]])
  })
})
