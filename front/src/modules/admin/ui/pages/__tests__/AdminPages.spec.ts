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
import { FakeAdminDashboardRepository } from '@/modules/admin/testing/FakeAdminDashboardRepository'
import { FakeAuditLogRepository } from '@/modules/admin/testing/FakeAuditLogRepository'
import { outOfStockMug } from '@/modules/catalog/testing/productFixtures'
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
      screen.getAllByRole('link', { name: 'Avis' }).some((link) => link.getAttribute('href') === '/admin/reviews'),
    ).toBe(true)
    expect(
      screen.getAllByRole('link', { name: 'Utilisateurs' }).some((link) => link.getAttribute('href') === '/admin/users'),
    ).toBe(true)
    expect(
      screen.getAllByRole('link', { name: 'Journal' }).some((link) => link.getAttribute('href') === '/admin/audit'),
    ).toBe(true)
  })

  it('shows revenue, today\'s orders and low stock on the dashboard', async () => {
    const dashboardRepository = new FakeAdminDashboardRepository({
      revenue: { cents: 12345, currency: 'EUR' },
      today: { orders: 3, revenue: { cents: 4500, currency: 'EUR' } },
      lowStock: [
        {
          id: outOfStockMug.id,
          name: outOfStockMug.name,
          slug: outOfStockMug.slug,
          stock: 0,
        },
      ],
    })

    await renderApp({ path: '/admin', session: adminSession, dashboardRepository })

    await waitFor(() => {
      expect(screen.getByText(/123,45/).textContent).toMatch(/€/)
    })
    expect(screen.getByText('Chiffre d\'affaires')).toBeTruthy()
    expect(screen.getByText('Commandes du jour')).toBeTruthy()
    expect(screen.getByText('3')).toBeTruthy()
    expect(screen.getByText(/45,00/).textContent).toMatch(/€/)
    expect(screen.getByRole('link', { name: /Nuvora Mug/ }).getAttribute('href')).toBe(
      `/admin/products/${outOfStockMug.id}`,
    )
    expect(screen.getByText('Rupture de stock')).toBeTruthy()
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
      expect(screen.getByText('test@test.test')).toBeTruthy()
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
          categoryId: null,
          published: true,
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

  it('refuses to demote the last admin', async () => {
    const userDirectory = new FakeUserDirectory([adminUser])
    userDirectory.assignError = new ApiError(409, 'last_admin_account', 'The request conflicts with the current state.')

    await renderApp({
      path: '/admin/users',
      session: adminSession,
      userDirectory,
    })

    await waitFor(() => {
      expect(screen.getByLabelText(`Rôle de ${adminUser.email}`)).toBeTruthy()
    })

    await userEvent.selectOptions(screen.getByLabelText(`Rôle de ${adminUser.email}`), 'customer')

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Il doit rester au moins un administrateur.')
    })
  })

  it('lists reviews to hide and restore', async () => {
    const adminCatalogRepository = new FakeAdminCatalogRepository([], [
      {
        id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1',
        productId: nuvoraTee.id,
        productName: nuvoraTee.name,
        productSlug: nuvoraTee.slug,
        author: 'ada',
        rating: 1,
        body: 'Avis insultant.',
        createdAt: '2026-09-22T12:00:00+00:00',
        reported: true,
        hidden: false,
      },
    ])

    await renderApp({
      path: '/admin/reviews',
      session: adminSession,
      adminCatalogRepository,
    })

    await waitFor(() => {
      expect(screen.getByText('Avis insultant.')).toBeTruthy()
      expect(screen.getByText('Signalé')).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Masquer' }))

    await waitFor(() => {
      expect(adminCatalogRepository.hidden).toEqual(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'])
      expect(screen.getByText('Masqué')).toBeTruthy()
      expect(screen.getByRole('button', { name: 'Restaurer' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Restaurer' }))

    await waitFor(() => {
      expect(adminCatalogRepository.restored).toEqual(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'])
      expect(screen.queryByText('Masqué')).toBeNull()
    })
  })

  it('lists who changed a role, a price, a stock and a payment', async () => {
    const auditLogRepository = new FakeAuditLogRepository([
      {
        id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1',
        action: 'role_changed',
        actorId: adminUser.id,
        actorEmail: adminUser.email,
        subjectType: 'user',
        subjectId: visitorUser.id,
        metadata: { from: 'customer', to: 'admin' },
        createdAt: '2026-10-27T12:00:00+00:00',
      },
      {
        id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2',
        action: 'price_changed',
        actorId: adminUser.id,
        actorEmail: adminUser.email,
        subjectType: 'product',
        subjectId: nuvoraTee.id,
        metadata: { fromCents: 1999, toCents: 2499 },
        createdAt: '2026-10-27T11:00:00+00:00',
      },
    ])

    await renderApp({
      path: '/admin/audit',
      session: adminSession,
      auditLogRepository,
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Journal' })).toBeTruthy()
      expect(screen.getByText('Rôle')).toBeTruthy()
      expect(screen.getByText('customer → admin')).toBeTruthy()
      expect(screen.getByText('Prix')).toBeTruthy()
    })
    expect(screen.getAllByText(adminUser.email).length).toBeGreaterThan(0)
    expect(screen.getByRole('link', { name: /19,99/ }).getAttribute('href')).toBe(`/admin/products/${nuvoraTee.id}`)
  })
})
