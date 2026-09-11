import { render, screen } from '@testing-library/vue'
import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, expect, it } from 'vitest'
import Pagination from '../Pagination.vue'

async function renderPagination(props: { page: number; limit: number; total: number }, path = '/') {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: { template: '<div />' } }],
  })
  await router.push(path)
  await router.isReady()

  return render(Pagination, {
    props,
    global: { plugins: [router] },
  })
}

describe('Pagination', () => {
  it('is hidden when everything fits on one page', async () => {
    await renderPagination({ page: 1, limit: 20, total: 5 })

    expect(screen.queryByRole('navigation', { name: 'Pagination' })).toBeNull()
  })

  it('links to the next page', async () => {
    await renderPagination({ page: 1, limit: 20, total: 45 })

    expect(screen.getByRole('link', { name: '2' }).getAttribute('href')).toBe('/?page=2')
    expect(screen.getByRole('link', { name: 'Page suivante' }).getAttribute('href')).toBe('/?page=2')
  })
})
