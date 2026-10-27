import { FetchHttpClient } from '@/shared/http/FetchHttpClient'
import { AuthHttpRepository } from '@/modules/auth/data/AuthHttpRepository'
import { createAuthSession } from '@/modules/auth/application/createAuthSession'
import { createCartState } from '@/modules/cart/application/createCartState'
import { restoreAuthSession } from '@/modules/auth/application/restoreAuthSession'
import { CartHttpRepository } from '@/modules/cart/data/CartHttpRepository'
import { createLocalGuestCart } from '@/modules/cart/data/guestCartStorage'
import { OrderHttpRepository } from '@/modules/order/data/OrderHttpRepository'
import { PaymentHttpRepository } from '@/modules/payment/data/PaymentHttpRepository'
import { AdminCatalogHttpRepository } from '@/modules/catalog/data/AdminCatalogHttpRepository'
import { AdminDashboardHttpRepository } from '@/modules/admin/data/AdminDashboardHttpRepository'
import { AuditLogHttpRepository } from '@/modules/admin/data/AuditLogHttpRepository'
import { createAddressBookState } from '@/modules/account/application/createAddressBookState'
import { AddressHttpRepository } from '@/modules/account/data/AddressHttpRepository'
import { createFavoritesState } from '@/modules/catalog/application/createFavoritesState'
import { FavoriteHttpRepository } from '@/modules/catalog/data/FavoriteHttpRepository'
import { createLocalGuestFavorites } from '@/modules/catalog/data/guestFavoriteStorage'
import { ProductHttpRepository } from '@/modules/catalog/data/ProductHttpRepository'
import { UserHttpDirectory } from '@/modules/auth/data/UserHttpDirectory'

window.localStorage.removeItem('shoppy.session')

export const authSession = createAuthSession()
export const httpClient = new FetchHttpClient(
  import.meta.env.VITE_API_URL ?? '/api',
  globalThis.fetch.bind(globalThis),
  authSession,
)
export const catalogRepository = new ProductHttpRepository(httpClient)
export const authRepository = new AuthHttpRepository(httpClient)
export const authReady = restoreAuthSession(authRepository, authSession)
export const cartRepository = new CartHttpRepository(httpClient)
export const cartState = createCartState(
  cartRepository,
  authSession.isAuthenticated,
  createLocalGuestCart(window.localStorage),
)
export const orderRepository = new OrderHttpRepository(httpClient)
export const paymentRepository = new PaymentHttpRepository(httpClient)
export const adminCatalogRepository = new AdminCatalogHttpRepository(httpClient)
export const adminDashboardRepository = new AdminDashboardHttpRepository(httpClient)
export const auditLogRepository = new AuditLogHttpRepository(httpClient)
export const userDirectory = new UserHttpDirectory(httpClient)
export const favoriteRepository = new FavoriteHttpRepository(httpClient)
export const favoritesState = createFavoritesState(
  favoriteRepository,
  authSession.isAuthenticated,
  createLocalGuestFavorites(window.localStorage),
)
export const addressBookRepository = new AddressHttpRepository(httpClient)
export const addressBookState = createAddressBookState(
  addressBookRepository,
  authSession.isAuthenticated,
)
