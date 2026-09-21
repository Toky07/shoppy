<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { toApiError } from '@/shared/http/toApiError'
import { formatDate } from '@/shared/datetime/formatDate'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { userInitials } from '@/shared/text/userInitials'
import type { User, UserRole } from '@/modules/auth/domain/User'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { DEFAULT_USER_LIMIT } from '@/modules/auth/application/defaultUserLimit'
import { userDirectoryKey } from '@/modules/auth/application/userDirectoryKey'
import { useUserList } from '@/modules/auth/application/useUserList'
import { userRoleLabel } from '@/modules/auth/ui/userRoleLabel'
import AdminPageHeader from './AdminPageHeader.vue'

const directory = inject(userDirectoryKey)
const session = inject(authSessionKey)

if (!directory) {
  throw new Error('User directory is not provided.')
}

const users = directory
const router = useRouter()
const route = useRoute()
const currentUserId = computed(() => session?.session.value?.user.id)
const search = computed(() => parseSearchQuery(route.query.q))
const searchInput = ref(search.value)
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_USER_LIMIT,
  search: search.value || undefined,
}))
const { status, page, error, reload } = useUserList(users, query)
const pendingId = ref<string>()
const errorMessage = ref<string>()
const successMessage = ref<string>()

watch(search, (value) => {
  if (value !== searchInput.value.trim()) {
    searchInput.value = value
  }
})

async function onSearch() {
  const next = searchInput.value.trim()
  const queryParams: Record<string, string> = {}
  if (next !== '') {
    queryParams.q = next
  }
  await router.push({ path: '/admin/users', query: queryParams })
}

function roleClass(role: UserRole) {
  return role === 'admin' ? 'badge-accent' : 'badge-neutral'
}

async function onRoleChange(user: User, event: Event) {
  const role = (event.target as HTMLSelectElement).value as UserRole
  if (role === user.role) {
    return
  }

  pendingId.value = user.id
  errorMessage.value = undefined
  successMessage.value = undefined
  try {
    const updated = await users.assignRole(user.id, role)
    if (page.value) {
      page.value = {
        ...page.value,
        items: page.value.items.map((item) => (item.id === user.id ? updated : item)),
      }
    }
    successMessage.value = `Rôle mis à jour pour ${updated.email}.`
  } catch (caught) {
    errorMessage.value = toApiError(caught).message
    await reload()
  } finally {
    pendingId.value = undefined
  }
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      title="Utilisateurs"
      icon="users"
      description="Tous les comptes de la boutique, avec leur rôle et leur date d'inscription."
    />

    <form class="mt-6 flex flex-wrap items-center gap-3" @submit.prevent="onSearch">
      <label class="relative min-w-0 flex-1">
        <span class="sr-only">Rechercher un email</span>
        <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
          <AppIcon name="search" :size="16" />
        </span>
        <input
          v-model="searchInput"
          type="search"
          class="field pl-11"
          placeholder="Rechercher par email…"
        />
      </label>
      <button type="submit" class="btn-outline">Filtrer</button>
      <p v-if="page" class="numeric text-sm text-muted">
        {{ page.total }} compte{{ page.total > 1 ? 's' : '' }}
      </p>
    </form>

    <StatusNotice v-if="errorMessage" tone="danger" class="mt-5">{{ errorMessage }}</StatusNotice>
    <StatusNotice v-if="successMessage" tone="positive" class="mt-5">{{ successMessage }}</StatusNotice>

    <div class="mt-5">
      <PageStatus :status="status" :error-message="error?.message" skeleton="rows">
        <template #empty>
          <EmptyState
            icon="users"
            :title="search ? 'Aucun résultat' : 'Aucun utilisateur'"
            :description="
              search
                ? `Aucun compte ne correspond à « ${search} ».`
                : 'Les comptes apparaîtront ici dès la première inscription.'
            "
          />
        </template>

        <div v-if="page" class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Compte</th>
                <th>Rôle</th>
                <th>Inscription</th>
                <th class="w-56">Accès</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in page.items" :key="user.id">
                <td>
                  <div class="flex items-center gap-3">
                    <span
                      class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-soft text-xs font-bold text-accent-fg"
                      aria-hidden="true"
                    >
                      {{ userInitials(user.email) }}
                    </span>
                    <div class="min-w-0">
                      <p class="truncate font-semibold text-strong">
                        {{ user.email }}
                        <span
                          v-if="user.id === currentUserId"
                          class="ml-2 align-middle text-[0.65rem] font-semibold tracking-[0.1em] text-accent-fg uppercase"
                        >
                          Vous
                        </span>
                      </p>
                      <p class="numeric truncate text-xs text-faint">{{ user.id }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <span :class="roleClass(user.role)">{{ userRoleLabel(user.role) }}</span>
                </td>
                <td class="text-sm text-muted">{{ formatDate(user.createdAt) }}</td>
                <td>
                  <label class="block">
                    <span class="sr-only">Rôle de {{ user.email }}</span>
                    <div class="relative">
                      <select
                        class="field appearance-none py-2 pr-10 font-medium"
                        :value="user.role"
                        :disabled="pendingId === user.id"
                        @change="onRoleChange(user, $event)"
                      >
                        <option value="customer">Client</option>
                        <option value="admin">Administrateur</option>
                      </select>
                      <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-faint">
                        <AppIcon name="chevron-down" :size="14" />
                      </span>
                    </div>
                  </label>
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
