<script setup lang="ts">
import { inject, ref } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { toApiError } from '@/shared/http/toApiError'
import type { User, UserRole } from '@/modules/auth/domain/User'
import { userDirectoryKey } from '@/modules/auth/application/userDirectoryKey'
import { userInitials } from '@/shared/text/userInitials'
import AdminGate from './AdminGate.vue'
import AdminPageHeader from './AdminPageHeader.vue'

const directory = inject(userDirectoryKey)

if (!directory) {
  throw new Error('User directory is not provided.')
}

const users = directory
const userId = ref('')
const user = ref<User | null>(null)
const role = ref<UserRole>('customer')
const pending = ref(false)
const errorMessage = ref<string>()
const successMessage = ref<string>()

async function onLoad() {
  pending.value = true
  errorMessage.value = undefined
  successMessage.value = undefined
  user.value = null
  try {
    const loaded = await users.getById(userId.value.trim())
    user.value = loaded
    role.value = loaded.role
  } catch (caught) {
    errorMessage.value =
      toApiError(caught).code === 'user_not_found'
        ? 'Cet utilisateur est introuvable.'
        : toApiError(caught).message
  } finally {
    pending.value = false
  }
}

async function onSave() {
  if (!user.value) {
    return
  }
  pending.value = true
  errorMessage.value = undefined
  successMessage.value = undefined
  try {
    user.value = await users.assignRole(user.value.id, role.value)
    successMessage.value = 'Rôle mis à jour.'
  } catch (caught) {
    errorMessage.value = toApiError(caught).message
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      eyebrow="Console"
      title="Utilisateurs"
      icon="users"
      description="Rechercher un compte par identifiant et ajuster son rôle."
      back-to="/admin"
      back-label="Retour à l'administration"
    />

    <AdminGate redirect="/admin/users">
      <div class="mt-10 grid max-w-4xl gap-6 lg:grid-cols-2">
        <form class="panel h-fit space-y-5 p-6 sm:p-7" @submit.prevent="onLoad">
          <h2 class="font-display text-lg font-bold text-strong">Rechercher un compte</h2>

          <label class="block">
            <span class="field-label">Identifiant</span>
            <input
              v-model="userId"
              required
              class="field numeric"
              placeholder="00000000-0000-0000-0000-000000000000"
            />
          </label>

          <button type="submit" class="btn-primary w-full" :disabled="pending">
            <AppIcon name="search" :size="16" />
            Charger
          </button>

          <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
          <StatusNotice v-if="successMessage" tone="positive">{{ successMessage }}</StatusNotice>
        </form>

        <form v-if="user" class="panel h-fit space-y-5 p-6 sm:p-7" @submit.prevent="onSave">
          <div class="flex items-center gap-3">
            <span
              class="flex size-10 items-center justify-center rounded-full bg-accent-soft text-xs font-bold text-accent-fg"
              aria-hidden="true"
              >{{ userInitials(user.email) }}</span
            >
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-strong">{{ user.email }}</p>
              <p class="numeric truncate text-xs text-faint">{{ user.id }}</p>
            </div>
          </div>

          <label class="block">
            <span class="field-label">Rôle</span>
            <div class="relative">
              <select v-model="role" class="field appearance-none pr-10 font-medium">
                <option value="customer">Client</option>
                <option value="admin">Administrateur</option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-faint">
                <AppIcon name="chevron-down" :size="14" />
              </span>
            </div>
          </label>

          <button type="submit" class="btn-primary w-full" :disabled="pending">
            <AppIcon name="check" :size="16" />
            Enregistrer
          </button>
        </form>
      </div>
    </AdminGate>
  </section>
</template>
