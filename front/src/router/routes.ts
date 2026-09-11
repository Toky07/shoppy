import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'catalog', component: () => import('@/modules/catalog/ui/ProductListPage.vue') },
  { path: '/products/:id', name: 'product', component: () => import('@/modules/catalog/ui/ProductDetailPage.vue') },
  { path: '/cart', name: 'cart', component: () => import('@/modules/cart/ui/CartPage.vue') },
  { path: '/orders', name: 'orders', component: () => import('@/modules/order/ui/OrderListPage.vue') },
  { path: '/orders/:id', name: 'order', component: () => import('@/modules/order/ui/OrderDetailPage.vue') },
  { path: '/login', name: 'login', component: () => import('@/modules/auth/ui/LoginPage.vue') },
  { path: '/register', name: 'register', component: () => import('@/modules/auth/ui/RegisterPage.vue') },
  { path: '/admin', name: 'admin', component: () => import('@/modules/admin/ui/AdminHomePage.vue') },
  { path: '/admin/orders', name: 'admin-orders', component: () => import('@/modules/admin/ui/AdminOrderListPage.vue') },
  { path: '/admin/orders/:id', name: 'admin-order', component: () => import('@/modules/admin/ui/AdminOrderDetailPage.vue') },
  { path: '/admin/products', name: 'admin-products', component: () => import('@/modules/admin/ui/AdminProductListPage.vue') },
  { path: '/admin/products/new', name: 'admin-product-new', component: () => import('@/modules/admin/ui/AdminProductFormPage.vue') },
  { path: '/admin/products/:id', name: 'admin-product-edit', component: () => import('@/modules/admin/ui/AdminProductFormPage.vue') },
  { path: '/admin/users', name: 'admin-users', component: () => import('@/modules/admin/ui/AdminUserPage.vue') },
]
