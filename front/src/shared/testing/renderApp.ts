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
import { createFakeCatalogRepository } from '@/modules/catalog/testing/fakeCatalogRepository'
import type { Session } from '@/modules/auth/domain/Session'
import { routes } from '@/router/routes'

export async function renderApp(options: {
  repository?: CatalogRepository
  authRepository?: AuthRepository
  cartRepository?: CartRepository
  orderRepository?: OrderRepository
  paymentRepository?: PaymentRepository
  adminCatalogRepository?: AdminCatalogRepository
  userDirectory?: UserDirectory
  session?: Session | null
  path?: string
} = {}) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  const authSession = createAuthSession(new MemorySessionStore(options.session ?? null))
  const cartRepository = options.cartRepository ?? new FakeCartRepository()
  const cartState = createCartState(cartRepository, authSession.isAuthenticated)

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
        [userDirectoryKey as symbol]: options.userDirectory ?? new FakeUserDirectory(),
      },
    },
  })

  return { ...view, router, authSession, cartState }
}
