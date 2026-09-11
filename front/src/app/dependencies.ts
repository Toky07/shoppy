import { FetchHttpClient } from '@/shared/http/FetchHttpClient'
import { AuthHttpRepository } from '@/modules/auth/data/AuthHttpRepository'
import { createAuthSession } from '@/modules/auth/application/createAuthSession'
import { createCartState } from '@/modules/cart/application/createCartState'
import { LocalStorageSessionStore } from '@/modules/auth/data/LocalStorageSessionStore'
import { CartHttpRepository } from '@/modules/cart/data/CartHttpRepository'
import { OrderHttpRepository } from '@/modules/order/data/OrderHttpRepository'
import { PaymentHttpRepository } from '@/modules/payment/data/PaymentHttpRepository'
import { AdminCatalogHttpRepository } from '@/modules/catalog/data/AdminCatalogHttpRepository'
import { ProductHttpRepository } from '@/modules/catalog/data/ProductHttpRepository'
import { UserHttpDirectory } from '@/modules/auth/data/UserHttpDirectory'

export const authSession = createAuthSession(new LocalStorageSessionStore(window.localStorage))
export const httpClient = new FetchHttpClient(
  import.meta.env.VITE_API_URL ?? '/api',
  globalThis.fetch.bind(globalThis),
  authSession,
)
export const catalogRepository = new ProductHttpRepository(httpClient)
export const authRepository = new AuthHttpRepository(httpClient)
export const cartRepository = new CartHttpRepository(httpClient)
export const cartState = createCartState(cartRepository, authSession.isAuthenticated)
export const orderRepository = new OrderHttpRepository(httpClient)
export const paymentRepository = new PaymentHttpRepository(httpClient)
export const adminCatalogRepository = new AdminCatalogHttpRepository(httpClient)
export const userDirectory = new UserHttpDirectory(httpClient)
