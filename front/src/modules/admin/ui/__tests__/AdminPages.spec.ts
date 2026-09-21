import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { adminSession, adminUser, visitorSession, visitorUser } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'
import { FakeOrderRepository } from '@/modules/order/testing/FakeOrderRepository'
import { pendingOrder } from '@/modules/order/testing/orderFixtures'
import { FakeAdminCatalogRepository } from '@/modules/catalog/testing/FakeAdminCatalogRepository'
import { createFakeCatalogRepository } from '@/modules/catalog/testing/fakeCatalogRepository'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'
import { FakeUserDirectory } from '@/modules/auth/testing/FakeUserDirectory'
import { ApiError } from '@/shared/http/ApiError'

describe('Admin pages', () => {
  it('redirects a guest to the console login', async () => {
    const { router } = await renderApp({ path: '/admin' })

    await waitFor(() => {
      expect(router.currentRoute.value.path).toBe('/admin/login')
    })
    expect(screen.getByRole('heading', { name: 'Connexion administrateur' })).toBeTruthy()
    expect(screen.getByRole('link', { name: 'Retour à la boutique' }).getAttribute('href')).toBe('/')
    expect(router.currentRoute.value.query.redirect).toBe('/admin')
  })

  it('blocks a customer', async () => {
    await renderApp({ path: '/admin', session: visitorSession })

    expect(screen.getByRole('alert').textContent).toContain(
      'Cette page est réservée aux administrateurs.',
    )
    expect(screen.queryByRole('navigation', { name: 'Navigation administration' })).toBeNull()
  })

  it('shows the console chrome without the storefront header', async () => {
    await renderApp({ path: '/admin', session: adminSession })

    expect(screen.getByRole('heading', { name: 'Tableau de bord' })).toBeTruthy()
    expect(screen.queryByRole('link', { name: 'Shoppy' })).toBeNull()
    expect(screen.getByRole('navigation', { name: 'Navigation administration' })).toBeTruthy()
    expect(
      screen.getAllByRole('link', { name: 'Commandes' }).some((link) => link.getAttribute('href') === '/admin/orders'),
    ).toBe(true)
    expect(
      screen.getAllByRole('link', { name: 'Catalogue' }).some((link) => link.getAttribute('href') === '/admin/products'),
    ).toBe(true)
    expect(
      screen.getAllByRole('link', { name: 'Utilisateurs' }).some((link) => link.getAttribute('href') === '/admin/users'),
    ).toBe(true)
  })

  it('signs an admin into the console', async () => {
    const authRepository = new FakeAuthRepository()
    authRepository.loginResult = adminSession
    const { router } = await renderApp({ authRepository, path: '/admin/login' })

    await userEvent.type(screen.getByLabelText('Adresse email'), 'admin@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password123')
    await userEvent.click(screen.getByRole('button', { name: 'Entrer dans la console' }))

    await waitFor(() => {
      expect(router.currentRoute.value.path).toBe('/admin')
      expect(screen.getByRole('heading', { name: 'Tableau de bord' })).toBeTruthy()
    })
  })

  it('rejects a customer on the console login', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, path: '/admin/login' })

    await userEvent.type(screen.getByLabelText('Adresse email'), 'visitor@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password123')
    await userEvent.click(screen.getByRole('button', { name: 'Entrer dans la console' }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain(
        'Cet espace est réservé aux administrateurs.',
      )
    })
    expect(screen.getByRole('button', { name: 'Se déconnecter' })).toBeTruthy()
  })

  it('shows invalid credentials on the console login', async () => {
    const authRepository = new FakeAuthRepository()
    authRepository.loginError = new ApiError(401, 'invalid_credentials', 'Invalid credentials.')
    await renderApp({ authRepository, path: '/admin/login' })

    await userEvent.type(screen.getByLabelText('Adresse email'), 'admin@shoppy.test')
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'password123')
    await userEvent.click(screen.getByRole('button', { name: 'Entrer dans la console' }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Identifiants invalides.')
    })
  })

  it('lists all orders for an admin', async () => {
    const orderRepository = new FakeOrderRepository([pendingOrder])
    await renderApp({
      path: '/admin/orders',
      session: adminSession,
      orderRepository,
    })

    await waitFor(() => {
      expect(screen.getByText(/En attente/)).toBeTruthy()
      expect(screen.getByText(`Client ${pendingOrder.customerId}`)).toBeTruthy()
    })
    expect(orderRepository.listAllCount).toBeGreaterThan(0)
    expect(
      screen.getByRole('link', { name: 'Voir la commande du 10 septembre 2026' }).getAttribute('href'),
    ).toBe(`/admin/orders/${pendingOrder.id}`)
  })

  it('marks an order as paid', async () => {
    const orderRepository = new FakeOrderRepository([pendingOrder])
    await renderApp({
      path: `/admin/orders/${pendingOrder.id}`,
      session: adminSession,
      orderRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Marquer comme payée' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Marquer comme payée' }))

    await waitFor(() => {
      expect(orderRepository.markedPaid).toEqual([pendingOrder.id])
      expect(screen.getByText('Payée')).toBeTruthy()
    })
  })

  it('lists products to edit', async () => {
    await renderApp({
      path: '/admin/products',
      session: adminSession,
      repository: createFakeCatalogRepository([nuvoraTee]),
    })

    await waitFor(() => {
      expect(screen.getByRole('link', { name: 'Modifier Nuvora Tee' }).getAttribute('href')).toBe(
        `/admin/products/${nuvoraTee.id}`,
      )
    })
    expect(
      screen.getByRole('link', { name: 'Nouveau produit' }).getAttribute('href'),
    ).toBe('/admin/products/new')
  })

  it('deletes a product from the catalog list', async () => {
    const adminCatalogRepository = new FakeAdminCatalogRepository([nuvoraTee])
    await renderApp({
      path: '/admin/products',
      session: adminSession,
      repository: createFakeCatalogRepository([nuvoraTee]),
      adminCatalogRepository,
    })

    vi.spyOn(window, 'confirm').mockReturnValue(true)

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Supprimer Nuvora Tee' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Supprimer Nuvora Tee' }))

    await waitFor(() => {
      expect(adminCatalogRepository.deleted).toEqual([nuvoraTee.id])
    })
  })

  it('creates a product', async () => {
    const adminCatalogRepository = new FakeAdminCatalogRepository()
    const { router } = await renderApp({
      path: '/admin/products/new',
      session: adminSession,
      adminCatalogRepository,
    })

    await userEvent.type(screen.getByLabelText('Nom'), 'Nuvora Hoodie')
    await userEvent.clear(screen.getByLabelText('Prix (€)'))
    await userEvent.type(screen.getByLabelText('Prix (€)'), '29.99')
    await userEvent.clear(screen.getByLabelText('Stock'))
    await userEvent.type(screen.getByLabelText('Stock'), '5')
    await userEvent.click(screen.getByRole('button', { name: 'Créer' }))

    await waitFor(() => {
      expect(adminCatalogRepository.created).toEqual([
        {
          name: 'Nuvora Hoodie',
          priceCents: 2999,
          description: null,
          stock: 5,
        },
      ])
      expect(router.currentRoute.value.path).toBe('/admin/products')
    })
  })

  it('updates and deletes a product', async () => {
    const adminCatalogRepository = new FakeAdminCatalogRepository([nuvoraTee])
    const { router } = await renderApp({
      path: `/admin/products/${nuvoraTee.id}`,
      session: adminSession,
      repository: createFakeCatalogRepository([nuvoraTee]),
      adminCatalogRepository,
    })

    await waitFor(() => {
      expect((screen.getByLabelText('Nom') as HTMLInputElement).value).toBe('Nuvora Tee')
    })

    await userEvent.clear(screen.getByLabelText('Nom'))
    await userEvent.type(screen.getByLabelText('Nom'), 'Nuvora Tee 2')
    await userEvent.click(screen.getByRole('button', { name: 'Enregistrer' }))

    await waitFor(() => {
      expect(adminCatalogRepository.updated[0]).toMatchObject({
        id: nuvoraTee.id,
        input: { name: 'Nuvora Tee 2' },
      })
      expect(adminCatalogRepository.stockSets).toEqual([{ id: nuvoraTee.id, stock: 10 }])
      expect(router.currentRoute.value.path).toBe('/admin/products')
    })

    await renderApp({
      path: `/admin/products/${nuvoraTee.id}`,
      session: adminSession,
      repository: createFakeCatalogRepository([nuvoraTee]),
      adminCatalogRepository,
    })

    vi.spyOn(window, 'confirm').mockReturnValue(true)

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Supprimer' })).toBeTruthy()
    })
    await userEvent.click(screen.getByRole('button', { name: 'Supprimer' }))

    await waitFor(() => {
      expect(adminCatalogRepository.deleted).toEqual([nuvoraTee.id])
    })
  })

  it('lists users and assigns a role', async () => {
    const userDirectory = new FakeUserDirectory([visitorUser, adminUser])
    await renderApp({
      path: '/admin/users',
      session: adminSession,
      userDirectory,
    })

    await waitFor(() => {
      expect(screen.getByLabelText(`Rôle de ${visitorUser.email}`)).toBeTruthy()
      expect(screen.getByLabelText(`Rôle de ${adminUser.email}`)).toBeTruthy()
    })
    expect(userDirectory.listCount).toBeGreaterThan(0)

    await userEvent.selectOptions(screen.getByLabelText(`Rôle de ${visitorUser.email}`), 'admin')

    await waitFor(() => {
      expect(userDirectory.assigned).toEqual([{ id: visitorUser.id, role: 'admin' }])
      expect(screen.getByRole('status').textContent).toContain('Rôle mis à jour')
    })
  })
})
