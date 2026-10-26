import { describe, expect, it } from 'vitest'
import { ref } from 'vue'
import { createMemoryHistory, createRouter } from 'vue-router'
import { createNavigationGuard } from '../navigationGuard'
import { routes } from '../routes'

function authState(authenticated = false, admin = false) {
  return {
    isAuthenticated: ref(authenticated),
    isAdmin: ref(admin),
  }
}

async function open(path: string, session = authState()) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  router.beforeEach(createNavigationGuard(session))
  await router.push(path)
  await router.isReady()
  return router.currentRoute.value
}

describe('navigationGuard', () => {
  it('sends guests from account and orders to login', async () => {
    const account = await open('/account')
    expect(account.path).toBe('/login')
    expect(account.query.redirect).toBe('/account')

    const orders = await open('/orders')
    expect(orders.path).toBe('/login')
    expect(orders.query.redirect).toBe('/orders')

    const order = await open('/orders/550e8400-e29b-41d4-a716-446655440000')
    expect(order.path).toBe('/login')
    expect(order.query.redirect).toBe('/orders/550e8400-e29b-41d4-a716-446655440000')
  })

  it('sends guests from the console to the admin login', async () => {
    const to = await open('/admin/products')

    expect(to.path).toBe('/admin/login')
    expect(to.query.redirect).toBe('/admin/products')
  })

  it('lets customers open their account and keeps them out of the console chrome via the layout', async () => {
    const customer = authState(true, false)

    const account = await open('/account', customer)
    expect(account.path).toBe('/account')

    const admin = await open('/admin', customer)
    expect(admin.path).toBe('/admin')
  })

  it('lets admins open the console', async () => {
    const to = await open('/admin/users', authState(true, true))

    expect(to.path).toBe('/admin/users')
  })

  it('leaves the catalog public', async () => {
    const to = await open('/')

    expect(to.path).toBe('/')
  })
})
