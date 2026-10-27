import { render } from '@testing-library/vue'
import { createMemoryHistory, createRouter } from 'vue-router'
import App from '@/App.vue'
import type { AuthRepository } from '@/modules/auth/application/AuthRepository'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { createAuthSession } from '@/modules/auth/application/createAuthSession'
import type { CartRepository } from '@/modules/cart/application/CartRepository'
import { cartRepositoryKey } from '@/modules/cart/application/cartRepositoryKey'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { createCartState } from '@/modules/cart/application/createCartState'
import type { AdminCatalogRepository } from '@/modules/catalog/application/AdminCatalogRepository'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import type { AdminDashboardRepository } from '@/modules/admin/application/AdminDashboardRepository'
import { adminDashboardRepositoryKey } from '@/modules/admin/application/adminDashboardRepositoryKey'
import { FakeAdminDashboardRepository } from '@/modules/admin/testing/FakeAdminDashboardRepository'
import type { CatalogRepository } from '@/modules/catalog/application/CatalogRepository'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import type { OrderRepository } from '@/modules/order/application/OrderRepository'
import { orderRepositoryKey } from '@/modules/order/application/orderRepositoryKey'
import type { PaymentRepository } from '@/modules/payment/application/PaymentRepository'
import { paymentRepositoryKey } from '@/modules/payment/application/paymentRepositoryKey'
import type { UserDirectory } from '@/modules/auth/application/UserDirectory'
import { userDirectoryKey } from '@/modules/auth/application/userDirectoryKey'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'
import { FakeCartRepository } from '@/modules/cart/testing/FakeCartRepository'
import { FakeOrderRepository } from '@/modules/order/testing/FakeOrderRepository'
import { FakePaymentRepository } from '@/modules/payment/testing/FakePaymentRepository'
import { FakeAdminCatalogRepository } from '@/modules/catalog/testing/FakeAdminCatalogRepository'
import { FakeUserDirectory } from '@/modules/auth/testing/FakeUserDirectory'
import { MemorySessionStore } from '@/modules/auth/testing/MemorySessionStore'
import { createAddressBookState } from '@/modules/account/application/createAddressBookState'
import type { AddressBookRepository } from '@/modules/account/application/AddressBookRepository'
import { addressBookStateKey } from '@/modules/account/application/addressBookStateKey'
import { FakeAddressBookRepository } from '@/modules/account/testing/FakeAddressBookRepository'
import { createFavoritesState } from '@/modules/catalog/application/createFavoritesState'
import { favoritesStateKey } from '@/modules/catalog/application/favoritesStateKey'
import type { FavoriteRepository } from '@/modules/catalog/application/FavoriteRepository'
import { FakeFavoriteRepository } from '@/modules/catalog/testing/FakeFavoriteRepository'
import { createFakeCatalogRepository } from '@/modules/catalog/testing/fakeCatalogRepository'
import type { Session } from '@/modules/auth/domain/Session'
import { createNavigationGuard } from '@/router/navigationGuard'
import { routes } from '@/router/routes'

export async function renderApp(options: {
  repository?: CatalogRepository
  authRepository?: AuthRepository
  cartRepository?: CartRepository
  orderRepository?: OrderRepository
  paymentRepository?: PaymentRepository
  adminCatalogRepository?: AdminCatalogRepository
  dashboardRepository?: AdminDashboardRepository
  userDirectory?: UserDirectory
  favoriteRepository?: FavoriteRepository
  addressBookRepository?: AddressBookRepository
  session?: Session | null
  path?: string
} = {}) {
  const authSession = createAuthSession(new MemorySessionStore(options.session ?? null))
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  router.beforeEach(createNavigationGuard(authSession))
  const cartRepository = options.cartRepository ?? new FakeCartRepository()
  const cartState = createCartState(cartRepository, authSession.isAuthenticated)
  const favoritesState = createFavoritesState(
    options.favoriteRepository ?? new FakeFavoriteRepository(),
    authSession.isAuthenticated,
  )
  const addressBookState = createAddressBookState(
    options.addressBookRepository ?? new FakeAddressBookRepository(),
    authSession.isAuthenticated,
  )

  await router.push(options.path ?? '/')
  await router.isReady()

  const view = render(App, {
    global: {
      plugins: [router],
      provide: {
        [catalogRepositoryKey as symbol]: options.repository ?? createFakeCatalogRepository([]),
        [authRepositoryKey as symbol]: options.authRepository ?? new FakeAuthRepository(),
        [authSessionKey as symbol]: authSession,
        [cartRepositoryKey as symbol]: cartRepository,
        [cartStateKey as symbol]: cartState,
        [orderRepositoryKey as symbol]: options.orderRepository ?? new FakeOrderRepository(),
        [paymentRepositoryKey as symbol]: options.paymentRepository ?? new FakePaymentRepository(),
        [adminCatalogRepositoryKey as symbol]: options.adminCatalogRepository ?? new FakeAdminCatalogRepository(),
        [adminDashboardRepositoryKey as symbol]:
          options.dashboardRepository ?? new FakeAdminDashboardRepository(),
        [userDirectoryKey as symbol]: options.userDirectory ?? new FakeUserDirectory(),
        [favoritesStateKey as symbol]: favoritesState,
        [addressBookStateKey as symbol]: addressBookState,
      },
    },
  })

  return { ...view, router, authSession, cartState }
}
