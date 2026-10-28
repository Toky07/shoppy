<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { formatDate } from '@/shared/datetime/formatDate'
import { toApiError } from '@/shared/http/toApiError'
import { auditLogRepositoryKey } from '@/modules/admin/application/auditLogRepositoryKey'
import { DEFAULT_AUDIT_LIMIT } from '@/modules/admin/application/defaultAuditLimit'
import type { AuditLogPage } from '@/modules/admin/domain/AuditEntry'
import { useI18n } from '@/shared/i18n/useI18n'
import { auditActionLabel, auditDetail, auditSubject } from '../lib/auditActionLabel'
import AdminPageHeader from '../components/AdminPageHeader.vue'

const repository = inject(auditLogRepositoryKey)

if (!repository) {
  throw new Error('Audit log dependencies are not provided.')
}

const { t, intlLocale } = useI18n()
const log = repository
const route = useRoute()
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_AUDIT_LIMIT,
}))
const status = ref<'loading' | 'ready' | 'empty' | 'error'>('loading')
const page = ref<AuditLogPage>()
const errorMessage = ref<string>()

async function load() {
  status.value = 'loading'
  errorMessage.value = undefined
  try {
    const next = await log.list(query.value)
    page.value = next
    status.value = next.items.length === 0 ? 'empty' : 'ready'
  } catch (caught) {
    page.value = undefined
    errorMessage.value = toApiError(caught).message
    status.value = 'error'
  }
}

watch(query, load, { immediate: true })

function subjectTo(type: string, id: string): string | null {
  if (type === 'product') {
    return `/admin/products/${id}`
  }

  if (type === 'order') {
    return `/admin/orders/${id}`
  }

  if (type === 'user') {
    return '/admin/users'
  }

  return null
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      :title="t('admin.titles.audit')"
      icon="list"
      :description="t('admin.auditDescription')"
    />

    <div class="mt-6">
      <PageStatus :status="status" :error-message="errorMessage" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="list"
            :title="t('admin.noEntries')"
            :description="t('admin.auditEmpty')"
          />
        </template>

        <div v-if="page" class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>{{ t('admin.date') }}</th>
                <th>{{ t('admin.actor') }}</th>
                <th>{{ t('admin.action') }}</th>
                <th>{{ t('admin.subject') }}</th>
                <th>{{ t('admin.detail') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in page.items" :key="entry.id">
                <td class="font-semibold text-strong">{{ formatDate(entry.createdAt, intlLocale) }}</td>
                <td class="text-sm text-muted">{{ entry.actorEmail ?? t('admin.audit.system') }}</td>
                <td class="font-semibold text-strong">{{ auditActionLabel(entry.action, t) }}</td>
                <td>
                  <RouterLink
                    v-if="subjectTo(entry.subjectType, entry.subjectId)"
                    :to="subjectTo(entry.subjectType, entry.subjectId)!"
                    class="link-quiet font-medium text-strong"
                  >
                    {{ auditSubject(entry, t) }}
                  </RouterLink>
                  <span v-else class="font-medium text-strong">{{ auditSubject(entry, t) }}</span>
                </td>
                <td class="text-sm text-muted">{{ auditDetail(entry) }}</td>
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
