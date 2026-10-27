<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import { catalogRepositoryKey } from '@/modules/catalog/application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '@/modules/catalog/application/defaultProductLimit'
import { parseProductSort } from '@/modules/catalog/application/productSort'
import { useProductList } from '@/modules/catalog/application/useProductList'
import CatalogToolbar from '@/modules/catalog/ui/components/CatalogToolbar.vue'
import { useI18n } from '@/shared/i18n/useI18n'
import AdminPageHeader from '../components/AdminPageHeader.vue'
import AdminProductCard from '../components/AdminProductCard.vue'

const catalog = inject(catalogRepositoryKey)
const adminCatalog = inject(adminCatalogRepositoryKey)

if (!catalog || !adminCatalog) {
  throw new Error('Admin catalog dependencies are not provided.')
}

const { t } = useI18n()
const catalogRepository = catalog
const adminRepository = adminCatalog
const route = useRoute()
const search = computed(() => parseSearchQuery(route.query.q))
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_PRODUCT_LIMIT,
  search: search.value || undefined,
  sort: parseProductSort(route.query.sort),
  includeDrafts: true,
}))
const { status, page, error, reload } = useProductList(catalogRepository, query)
const pendingId = ref<string>()
const { pending, errorMessage, run } = usePendingAction((caught) => caught.message)

function onDelete(id: string, name: string) {
  if (!window.confirm(t('admin.deleteConfirm', { name }))) {
    return
  }

  return run(async () => {
    pendingId.value = id
    try {
      await adminRepository.delete(id)
      await reload()
    } finally {
      pendingId.value = undefined
    }
  })
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      :title="t('admin.titles.catalog')"
      icon="tag"
      :description="t('admin.catalogDescription')"
    >
      <template #actions>
        <RouterLink to="/admin/products/new" class="btn-primary">
          <AppIcon name="plus" :size="16" />
          {{ t('admin.newProduct') }}
        </RouterLink>
      </template>
    </AdminPageHeader>

    <div class="mt-6 space-y-5">
      <CatalogToolbar :total="page?.total" :show-view="false" />
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>

      <PageStatus :status="status" :error-message="error?.message" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="tag"
            :title="search ? t('catalog.noResults') : t('catalog.noProducts')"
            :description="
              search
                ? t('catalog.emptySearch', { search })
                : t('admin.noProducts')
            "
          />
        </template>

        <div v-if="page" class="admin-product-list">
          <div class="admin-product-list-head" aria-hidden="true">
            <span>{{ t('admin.product') }}</span>
            <span class="text-right">{{ t('admin.price') }}</span>
            <span>{{ t('admin.stock') }}</span>
            <span class="text-right">{{ t('admin.actions') }}</span>
          </div>
          <AdminProductCard
            v-for="product in page.items"
            :key="product.id"
            :product="product"
            :pending="pending && pendingId === product.id"
            @delete="onDelete(product.id, product.name)"
          />
        </div>

        <div v-if="page" class="mt-8 flex justify-center">
          <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
