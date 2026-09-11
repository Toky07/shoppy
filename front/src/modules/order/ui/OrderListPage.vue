<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { formatDate } from '@/shared/datetime/formatDate'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { orderRepositoryKey } from '../application/orderRepositoryKey'
import { DEFAULT_ORDER_LIMIT } from '../application/defaultOrderLimit'
import { useOrderList } from '../application/useOrderList'
import { orderErrorMessage } from './orderErrorMessage'
import { orderStatusLabel } from './orderStatusLabel'

const session = inject(authSessionKey)
const repository = inject(orderRepositoryKey)

if (!session || !repository) {
  throw new Error('Order dependencies are not provided.')
}

const authSession = session
const orderRepository = repository
const isAuthenticated = computed(() => authSession.isAuthenticated.value)
const route = useRoute()
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_ORDER_LIMIT,
}))
const { status, page, error } = useOrderList(orderRepository, query, isAuthenticated)
const loadError = computed(() => (error.value ? orderErrorMessage(error.value) : undefined))
</script>

<template>
  <section class="animate-fade-in">
    <div class="mb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900">Mes Commandes</h1>
      <p class="text-gray-500 mt-2">Suivez l'état de vos commandes et consultez votre historique.</p>
    </div>

    <template v-if="!isAuthenticated">
      <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center max-w-md mx-auto mt-12">
        <div class="h-20 w-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-600">
          <i class="fa-solid fa-lock text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Connexion requise</h2>
        <p class="text-gray-500 mb-8">Vous devez être connecté pour voir l'historique de vos commandes.</p>
        <RouterLink
          :to="{ path: '/login', query: { redirect: '/orders' } }"
          class="inline-block w-full rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all"
        >
          Se connecter
        </RouterLink>
      </div>
    </template>
    
    <div v-else class="mt-8">
      <PageStatus :status="status" :error-message="loadError">
        <template #empty>
          <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
            <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
              <i class="fa-solid fa-box-open text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Aucune commande</h3>
            <p class="text-gray-500 mb-8 max-w-sm">Vous n'avez pas encore passé de commande. Découvrez nos produits !</p>
            <RouterLink to="/" class="rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
              Explorer le catalogue
            </RouterLink>
          </div>
        </template>
        
        <div v-if="page" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="order in page.items"
            :key="order.id"
            class="group flex flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition-all"
          >
            <div class="flex justify-between items-start mb-4">
              <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                <i class="fa-regular fa-calendar"></i>
                {{ formatDate(order.createdAt) }}
              </div>
              <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-gray-100 text-gray-700">
                {{ orderStatusLabel(order.status) }}
              </span>
            </div>
            
            <div class="mb-6">
              <p class="text-sm text-gray-500 mb-1">Total de la commande</p>
              <p class="text-2xl font-black text-gray-900">
                <ProductPrice :price="order.total" />
              </p>
            </div>
            
            <div class="mt-auto pt-4 border-t border-gray-50">
              <RouterLink
                :to="{ name: 'order', params: { id: order.id } }"
                class="flex items-center justify-between text-sm font-bold text-indigo-600 group-hover:text-indigo-700 transition-colors"
              >
                Voir les détails
                <i class="fa-solid fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
              </RouterLink>
            </div>
          </article>
        </div>
        
        <div class="mt-12 flex justify-center">
          <Pagination
            v-if="page"
            :page="page.page"
            :limit="page.limit"
            :total="page.total"
          />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
