import type { RouteRecordRaw } from 'vue-router'
import AppLayout from '@/shared/ui/AppLayout.vue'
import AdminLayout from '@/modules/admin/ui/layouts/AdminLayout.vue'

export const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: AppLayout,
    children: [
      { path: '', name: 'catalog', component: () => import('@/modules/catalog/ui/pages/ProductListPage.vue') },
      { path: 'products/:slug', name: 'product', component: () => import('@/modules/catalog/ui/pages/ProductDetailPage.vue') },
      { path: 'favorites', name: 'favorites', component: () => import('@/modules/catalog/ui/pages/FavoritesPage.vue') },
      { path: 'cart', name: 'cart', component: () => import('@/modules/cart/ui/pages/CartPage.vue') },
      { path: 'orders', name: 'orders', component: () => import('@/modules/order/ui/pages/OrderListPage.vue') },
      { path: 'orders/:id', name: 'order', component: () => import('@/modules/order/ui/pages/OrderDetailPage.vue') },
      { path: 'account', name: 'account', component: () => import('@/modules/auth/ui/pages/AccountPage.vue') },
      { path: 'login', name: 'login', component: () => import('@/modules/auth/ui/pages/LoginPage.vue') },
      { path: 'register', name: 'register', component: () => import('@/modules/auth/ui/pages/RegisterPage.vue') },
      { path: 'forgot-password', name: 'forgot-password', component: () => import('@/modules/auth/ui/pages/ForgotPasswordPage.vue') },
      { path: 'reset-password', name: 'reset-password', component: () => import('@/modules/auth/ui/pages/ResetPasswordPage.vue') },
      { path: 'verify-email', name: 'verify-email', component: () => import('@/modules/auth/ui/pages/VerifyEmailPage.vue') },
      { path: 'confirm-email', name: 'confirm-email', component: () => import('@/modules/auth/ui/pages/ConfirmEmailPage.vue') },
    ],
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('@/modules/admin/ui/pages/AdminLoginPage.vue'),
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: '', name: 'admin', component: () => import('@/modules/admin/ui/pages/AdminHomePage.vue') },
      { path: 'orders', name: 'admin-orders', component: () => import('@/modules/admin/ui/pages/AdminOrderListPage.vue') },
      { path: 'orders/:id', name: 'admin-order', component: () => import('@/modules/admin/ui/pages/AdminOrderDetailPage.vue') },
      { path: 'products', name: 'admin-products', component: () => import('@/modules/admin/ui/pages/AdminProductListPage.vue') },
      {
        path: 'products/new',
        name: 'admin-product-new',
        component: () => import('@/modules/admin/ui/pages/AdminProductFormPage.vue'),
      },
      {
        path: 'products/:id',
        name: 'admin-product-edit',
        component: () => import('@/modules/admin/ui/pages/AdminProductFormPage.vue'),
      },
      { path: 'users', name: 'admin-users', component: () => import('@/modules/admin/ui/pages/AdminUserPage.vue') },
    ],
  },
]
