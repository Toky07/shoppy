import { createApp } from 'vue'
import App from './App.vue'
import {
    addressBookState,
    authReady,
    authRepository,
    authSession,
    adminCatalogRepository,
    adminDashboardRepository,
    cartRepository,
    cartState,
    catalogRepository,
    favoritesState,
    orderRepository,
    paymentRepository,
    userDirectory } from './app/dependencies'
import { addressBookStateKey } from './modules/account/application/addressBookStateKey'
import { authRepositoryKey } from './modules/auth/application/authRepositoryKey'
import { authSessionKey } from './modules/auth/application/authSessionKey'
import { userDirectoryKey } from './modules/auth/application/userDirectoryKey'
import { cartRepositoryKey } from './modules/cart/application/cartRepositoryKey'
import { cartStateKey } from './modules/cart/application/cartStateKey'
import { adminCatalogRepositoryKey } from './modules/catalog/application/adminCatalogRepositoryKey'
import { adminDashboardRepositoryKey } from './modules/admin/application/adminDashboardRepositoryKey'
import { catalogRepositoryKey } from './modules/catalog/application/catalogRepositoryKey'
import { favoritesStateKey } from './modules/catalog/application/favoritesStateKey'
import { orderRepositoryKey } from './modules/order/application/orderRepositoryKey'
import { paymentRepositoryKey } from './modules/payment/application/paymentRepositoryKey'
import router from './router'
import { createNavigationGuard } from './router/navigationGuard'
import '@fontsource-variable/inter'
import '@fontsource-variable/bricolage-grotesque/standard.css'
import './assets/main.css'

const app = createApp(App);

app.provide(catalogRepositoryKey, catalogRepository);
app.provide(authRepositoryKey, authRepository);
app.provide(authSessionKey, authSession);
app.provide(cartRepositoryKey, cartRepository);
app.provide(cartStateKey, cartState);
app.provide(orderRepositoryKey, orderRepository);
app.provide(paymentRepositoryKey, paymentRepository);
app.provide(adminCatalogRepositoryKey, adminCatalogRepository);
app.provide(adminDashboardRepositoryKey, adminDashboardRepository);
app.provide(userDirectoryKey, userDirectory);
app.provide(favoritesStateKey, favoritesState);
app.provide(addressBookStateKey, addressBookState);

await authReady;
router.beforeEach(createNavigationGuard(authSession));
app.use(router);
app.mount('#app');
