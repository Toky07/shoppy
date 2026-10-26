import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import { scrollBehavior } from './scrollBehavior'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior,
})

export default router
