import { createApp } from 'vue'
import App from './App.vue'
import {
    authReady,
    authRepository,
    authSession,
    adminCatalogRepository,
    cartRepository,
    cartState,
    catalogRepository,
    favoritesState,
    orderRepository,
    paymentRepository,
    userDirectory } from './app/dependencies'
import { authRepositoryKey } from './modules/auth/application/authRepositoryKey'
import { authSessionKey } from './modules/auth/application/authSessionKey'
import { userDirectoryKey } from './modules/auth/application/userDirectoryKey'
import { cartRepositoryKey } from './modules/cart/application/cartRepositoryKey'
import { cartStateKey } from './modules/cart/application/cartStateKey'
import { adminCatalogRepositoryKey } from './modules/catalog/application/adminCatalogRepositoryKey'
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
app.provide(userDirectoryKey, userDirectory);
app.provide(favoritesStateKey, favoritesState);

await authReady;
router.beforeEach(createNavigationGuard(authSession));
app.use(router);
app.mount('#app');
