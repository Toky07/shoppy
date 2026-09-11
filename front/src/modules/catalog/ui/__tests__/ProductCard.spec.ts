import { render, screen } from '@testing-library/vue'
import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, expect, it } from 'vitest'
import { routes } from '@/router/routes'
import ProductCard from '../ProductCard.vue'
import { nuvoraTee, outOfStockMug } from '../../testing/productFixtures'

async function renderCard(product = nuvoraTee) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  await router.push('/')
  await router.isReady()

  return render(ProductCard, {
    props: { product },
    global: { plugins: [router] },
  })
}

describe('ProductCard', () => {
  it('renders name, price, stock and a link to the product page', async () => {
    await renderCard()

    expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
    expect(screen.getByRole('img', { name: 'Nuvora Tee' }).getAttribute('src')).toBe(
      '/media/products/nuvora-tee.svg',
    )
    expect(screen.getByRole('link', { name: /Nuvora Tee/ }).getAttribute('href')).toBe(
      `/products/${nuvoraTee.id}`,
    )
    expect(screen.getByText(/19,99/)).toBeTruthy()
    expect(screen.getByText('En stock (10)')).toBeTruthy()
  })

  it('renders an out of stock label', async () => {
    await renderCard(outOfStockMug)

    expect(screen.getByText('Rupture de stock')).toBeTruthy()
  })
})
