import type { RouteRecordRaw } from 'vue-router'
import AppLayout from '@/shared/ui/AppLayout.vue'
import AdminLayout from '@/modules/admin/ui/AdminLayout.vue'

export const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: AppLayout,
    children: [
      { path: '', name: 'catalog', component: () => import('@/modules/catalog/ui/ProductListPage.vue') },
      { path: 'products/:slug', name: 'product', component: () => import('@/modules/catalog/ui/ProductDetailPage.vue') },
      { path: 'favorites', name: 'favorites', component: () => import('@/modules/catalog/ui/FavoritesPage.vue') },
      { path: 'cart', name: 'cart', component: () => import('@/modules/cart/ui/CartPage.vue') },
      { path: 'orders', name: 'orders', component: () => import('@/modules/order/ui/OrderListPage.vue') },
      { path: 'orders/:id', name: 'order', component: () => import('@/modules/order/ui/OrderDetailPage.vue') },
      { path: 'account', name: 'account', component: () => import('@/modules/auth/ui/AccountPage.vue') },
      { path: 'login', name: 'login', component: () => import('@/modules/auth/ui/LoginPage.vue') },
      { path: 'register', name: 'register', component: () => import('@/modules/auth/ui/RegisterPage.vue') },
      { path: 'forgot-password', name: 'forgot-password', component: () => import('@/modules/auth/ui/ForgotPasswordPage.vue') },
      { path: 'reset-password', name: 'reset-password', component: () => import('@/modules/auth/ui/ResetPasswordPage.vue') },
      { path: 'verify-email', name: 'verify-email', component: () => import('@/modules/auth/ui/VerifyEmailPage.vue') },
      { path: 'confirm-email', name: 'confirm-email', component: () => import('@/modules/auth/ui/ConfirmEmailPage.vue') },
    ],
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('@/modules/admin/ui/AdminLoginPage.vue'),
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: '', name: 'admin', component: () => import('@/modules/admin/ui/AdminHomePage.vue') },
      { path: 'orders', name: 'admin-orders', component: () => import('@/modules/admin/ui/AdminOrderListPage.vue') },
      { path: 'orders/:id', name: 'admin-order', component: () => import('@/modules/admin/ui/AdminOrderDetailPage.vue') },
      { path: 'products', name: 'admin-products', component: () => import('@/modules/admin/ui/AdminProductListPage.vue') },
      {
        path: 'products/new',
        name: 'admin-product-new',
        component: () => import('@/modules/admin/ui/AdminProductFormPage.vue'),
      },
      {
        path: 'products/:id',
        name: 'admin-product-edit',
        component: () => import('@/modules/admin/ui/AdminProductFormPage.vue'),
      },
      { path: 'users', name: 'admin-users', component: () => import('@/modules/admin/ui/AdminUserPage.vue') },
    ],
  },
]
