<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
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
import AdminPageHeader from './AdminPageHeader.vue'

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
  <section class="animate-fade-in">
    <AdminPageHeader
      eyebrow="Console"
      title="Catalogue"
      icon="tag"
      description="Les produits en vente, leurs prix et leurs stocks."
      back-to="/admin"
      back-label="Retour à l'administration"
    >
      <template #actions>
        <RouterLink to="/admin/products/new" class="btn-primary">
          <AppIcon name="plus" :size="16" />
          Nouveau produit
        </RouterLink>
      </template>
    </AdminPageHeader>

    <AdminGate redirect="/admin/products">
      <div class="mt-10 space-y-6">
        <CatalogToolbar :total="page?.total" :show-view="false" />

        <PageStatus
          :status="isAdmin ? status : 'loading'"
          :error-message="error?.message"
          skeleton="rows"
        >
          <template #empty>
            <EmptyState
              icon="tag"
              :title="search ? 'Aucun résultat' : 'Aucun produit'"
              :description="
                search
                  ? `Aucun produit ne correspond à « ${search} ».`
                  : 'Aucun produit pour le moment.'
              "
            />
          </template>

          <ul v-if="page" class="space-y-3">
            <li
              v-for="product in page.items"
              :key="product.id"
              class="panel flex flex-wrap items-center justify-between gap-5 p-5"
            >
              <div class="min-w-48 flex-1">
                <p class="font-semibold text-strong">{{ product.name }}</p>
                <p class="numeric mt-1 text-xs text-muted">
                  <ProductPrice :price="product.price" />
                </p>
              </div>

              <span :class="product.stock > 0 ? 'badge-neutral' : 'badge-danger'">
                <AppIcon name="box" :size="12" />
                Stock {{ product.stock }}
              </span>

              <RouterLink
                :to="{ name: 'admin-product-edit', params: { id: product.id } }"
                class="btn-outline btn-sm"
              >
                <AppIcon name="settings" :size="14" />
                Modifier <span class="sr-only">{{ product.name }}</span>
              </RouterLink>
            </li>
          </ul>

          <div v-if="page" class="mt-10 flex justify-center">
            <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
          </div>
        </PageStatus>
      </div>
    </AdminGate>
  </section>
</template>
