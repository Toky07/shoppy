<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '@/modules/catalog/application/defaultProductLimit'
import { parseProductSort } from '@/modules/catalog/application/productSort'
import { useProductList } from '@/modules/catalog/application/useProductList'
import CatalogToolbar from '@/modules/catalog/ui/CatalogToolbar.vue'
import AdminGate from './AdminGate.vue'

const session = inject(authSessionKey)
const repository = inject(catalogRepositoryKey)

if (!session || !repository) {
  throw new Error('Admin catalog dependencies are not provided.')
}

const isAdmin = computed(() => session.isAdmin.value)
const route = useRoute()
const search = computed(() => parseSearchQuery(route.query.q))
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_PRODUCT_LIMIT,
  search: search.value || undefined,
  sort: parseProductSort(route.query.sort),
}))
const { status, page, error } = useProductList(repository, query)
</script>

<template>
  <section>
    <p class="mb-6 text-sm">
      <RouterLink to="/admin" class="text-stone-600 hover:text-stone-900">Retour à l'administration</RouterLink>
    </p>
    <h1 class="text-2xl font-semibold tracking-tight">Catalogue</h1>
    <AdminGate redirect="/admin/products">
      <p class="mt-4">
        <RouterLink to="/admin/products/new" class="text-stone-900 underline">Nouveau produit</RouterLink>
      </p>
      <div class="mt-6 space-y-6">
        <CatalogToolbar :total="page?.total" />
        <PageStatus :status="isAdmin ? status : 'loading'" :error-message="error?.message">
          <template #empty>
            {{ search ? `Aucun produit ne correspond à « ${search} ».` : 'Aucun produit pour le moment.' }}
          </template>
          <ul v-if="page" class="space-y-3">
            <li
              v-for="product in page.items"
              :key="product.id"
              class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 bg-white p-4"
            >
              <div>
                <p class="font-semibold">{{ product.name }}</p>
                <p class="text-sm text-stone-600">
                  <ProductPrice :price="product.price" />
                  · stock {{ product.stock }}
                </p>
              </div>
              <RouterLink
                :to="{ name: 'admin-product-edit', params: { id: product.id } }"
                class="text-sm underline"
              >
                Modifier {{ product.name }}
              </RouterLink>
            </li>
          </ul>
          <Pagination
            v-if="page"
            :page="page.page"
            :limit="page.limit"
            :total="page.total"
          />
        </PageStatus>
      </div>
    </AdminGate>
  </section>
</template>
