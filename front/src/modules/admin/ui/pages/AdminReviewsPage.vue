<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { formatDate } from '@/shared/datetime/formatDate'
import { toApiError } from '@/shared/http/toApiError'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { adminCatalogRepositoryKey } from '@/modules/catalog/application/adminCatalogRepositoryKey'
import { DEFAULT_ADMIN_REVIEW_LIMIT } from '@/modules/catalog/application/defaultAdminReviewLimit'
import type { AdminReviewPage } from '@/modules/catalog/domain/AdminReview'
import { useI18n } from '@/shared/i18n/useI18n'
import AdminPageHeader from '../components/AdminPageHeader.vue'

const session = inject(authSessionKey)
const repository = inject(adminCatalogRepositoryKey)

if (!session || !repository) {
  throw new Error('Admin review dependencies are not provided.')
}

const { t, intlLocale } = useI18n()
const catalog = repository
const route = useRoute()
const reportedOnly = computed(() => route.query.reported === '1')
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_ADMIN_REVIEW_LIMIT,
  reportedOnly: reportedOnly.value,
}))
const status = ref<'loading' | 'ready' | 'empty' | 'error'>('loading')
const page = ref<AdminReviewPage>()
const errorMessage = ref<string>()
const actionError = ref<string>()
const pendingId = ref<string>()

async function load() {
  status.value = 'loading'
  errorMessage.value = undefined
  try {
    const next = await catalog.listReviews(query.value)
    page.value = next
    status.value = next.items.length === 0 ? 'empty' : 'ready'
  } catch (caught) {
    page.value = undefined
    errorMessage.value = toApiError(caught).message
    status.value = 'error'
  }
}

watch(query, load, { immediate: true })

async function onHide(id: string) {
  pendingId.value = id
  actionError.value = undefined
  try {
    await catalog.hideReview(id)
    await load()
  } catch (caught) {
    actionError.value = toApiError(caught).message
  } finally {
    pendingId.value = undefined
  }
}

async function onRestore(id: string) {
  pendingId.value = id
  actionError.value = undefined
  try {
    await catalog.restoreReview(id)
    await load()
  } catch (caught) {
    actionError.value = toApiError(caught).message
  } finally {
    pendingId.value = undefined
  }
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      :title="t('admin.titles.reviews')"
      icon="star"
      :description="t('admin.reviewsDescription')"
    >
      <template #actions>
        <RouterLink
          v-if="reportedOnly"
          :to="{ path: '/admin/reviews' }"
          class="btn-outline btn-sm"
        >
          {{ t('admin.allReviews') }}
        </RouterLink>
        <RouterLink
          v-else
          :to="{ path: '/admin/reviews', query: { reported: '1' } }"
          class="btn-outline btn-sm"
        >
          {{ t('admin.reportedOnly') }}
        </RouterLink>
      </template>
    </AdminPageHeader>

    <div class="mt-6">
      <StatusNotice v-if="actionError" tone="danger" class="mb-4">{{ actionError }}</StatusNotice>
      <PageStatus :status="status" :error-message="errorMessage" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="star"
            :title="t('admin.noReviews')"
            :description="t('admin.reviewsEmpty')"
          />
        </template>

        <div v-if="page" class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>{{ t('admin.date') }}</th>
                <th>{{ t('admin.product') }}</th>
                <th>{{ t('admin.author') }}</th>
                <th>{{ t('admin.review') }}</th>
                <th>{{ t('admin.status') }}</th>
                <th class="w-40"><span class="sr-only">{{ t('admin.actions') }}</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="review in page.items" :key="review.id">
                <td class="font-semibold text-strong">{{ formatDate(review.createdAt, intlLocale) }}</td>
                <td>
                  <RouterLink
                    :to="{ path: `/products/${review.productSlug}` }"
                    class="font-semibold link-quiet"
                  >
                    {{ review.productName }}
                  </RouterLink>
                </td>
                <td class="text-sm text-muted">{{ review.author }}</td>
                <td>
                  <p class="text-sm font-semibold text-strong">{{ review.rating }}/5</p>
                  <p class="mt-1 max-w-sm text-sm leading-relaxed text-body">{{ review.body }}</p>
                </td>
                <td>
                  <span v-if="review.reported" class="badge-accent">{{ t('reviews.reported') }}</span>
                  <span v-if="review.hidden" class="badge-neutral ml-1">{{ t('admin.hidden') }}</span>
                  <span v-if="!review.reported && !review.hidden" class="text-sm text-muted">{{ t('admin.published') }}</span>
                </td>
                <td class="text-right">
                  <button
                    v-if="review.hidden"
                    type="button"
                    class="btn-outline btn-sm"
                    :disabled="pendingId === review.id"
                    @click="onRestore(review.id)"
                  >
                    {{ t('admin.restore') }}
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn-outline btn-sm"
                    :disabled="pendingId === review.id"
                    @click="onHide(review.id)"
                  >
                    {{ t('admin.hide') }}
                  </button>
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
