import { describe, expect, it } from 'vitest'
import { adminRedirectPath } from '../adminRedirectPath'
import { adminPageTitle, isAdminNavActive, ADMIN_NAV } from '../adminNav'

describe('adminRedirectPath', () => {
  it('keeps a console path', () => {
    expect(adminRedirectPath('/admin/orders')).toBe('/admin/orders')
  })

  it('rejects the login page and foreign paths', () => {
    expect(adminRedirectPath('/admin/login')).toBe('/admin')
    expect(adminRedirectPath('/cart')).toBe('/admin')
    expect(adminRedirectPath('https://evil.test')).toBe('/admin')
  })
})

describe('adminNav', () => {
  it('marks nested catalog routes as active', () => {
    const catalog = ADMIN_NAV.find((item) => item.to === '/admin/products')
    expect(catalog).toBeTruthy()
    expect(isAdminNavActive('/admin/products/new', catalog!)).toBe(true)
    expect(isAdminNavActive('/admin', catalog!)).toBe(false)
  })

  it('keeps the dashboard exact', () => {
    const home = ADMIN_NAV[0]!
    expect(isAdminNavActive('/admin', home)).toBe(true)
    expect(isAdminNavActive('/admin/orders', home)).toBe(false)
  })

  it('titles nested pages', () => {
    expect(adminPageTitle('/admin')).toBe('Tableau de bord')
    expect(adminPageTitle('/admin/orders/abc')).toBe('Détail commande')
    expect(adminPageTitle('/admin/products/new')).toBe('Nouveau produit')
  })
})
