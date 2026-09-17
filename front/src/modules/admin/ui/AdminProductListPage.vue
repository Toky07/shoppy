<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import AppImage from '@/shared/ui/AppImage.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '@/modules/catalog/application/defaultProductLimit'
import { parseProductSort } from '@/modules/catalog/application/productSort'
import { useProductList } from '@/modules/catalog/application/useProductList'
import CatalogToolbar from '@/modules/catalog/ui/CatalogToolbar.vue'
import AdminPageHeader from './AdminPageHeader.vue'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('Admin catalog dependencies are not provided.')
}

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
      title="Catalogue"
      icon="tag"
      description="Les produits en vente, leurs prix et leurs stocks."
    >
      <template #actions>
        <RouterLink to="/admin/products/new" class="btn-primary">
          <AppIcon name="plus" :size="16" />
          Nouveau produit
        </RouterLink>
      </template>
    </AdminPageHeader>

    <div class="mt-6 space-y-5">
      <CatalogToolbar :total="page?.total" :show-view="false" />

      <PageStatus :status="status" :error-message="error?.message" skeleton="rows">
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

        <div v-if="page" class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th>Stock</th>
                <th class="w-36"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in page.items" :key="product.id">
                <td>
                  <div class="flex items-center gap-3">
                    <div class="size-11 overflow-hidden rounded-lg border border-line">
                      <AppImage :src="product.imageUrl" :alt="product.name" />
                    </div>
                    <span class="font-semibold text-strong">{{ product.name }}</span>
                  </div>
                </td>
                <td class="numeric text-muted">
                  <ProductPrice :price="product.price" />
                </td>
                <td>
                  <span :class="product.stock > 0 ? 'badge-neutral' : 'badge-danger'">
                    <AppIcon name="box" :size="12" />
                    Stock {{ product.stock }}
                  </span>
                </td>
                <td class="text-right">
                  <RouterLink
                    :to="{ name: 'admin-product-edit', params: { id: product.id } }"
                    class="btn-outline btn-sm"
                  >
                    <AppIcon name="settings" :size="14" />
                    Modifier <span class="sr-only">{{ product.name }}</span>
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="page" class="mt-8 flex justify-center">
          <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
