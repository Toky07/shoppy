<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { toApiError } from '@/shared/http/toApiError'
import type { User, UserRole } from '@/modules/auth/domain/User'
import { userDirectoryKey } from '@/modules/auth/application/userDirectoryKey'
import AdminGate from './AdminGate.vue'

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
      toApiError(caught).code === 'user_not_found' ? 'Cet utilisateur est introuvable.' : toApiError(caught).message
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
  <section>
    <p class="mb-6 text-sm">
      <RouterLink to="/admin" class="text-stone-600 hover:text-stone-900">Retour à l'administration</RouterLink>
    </p>
    <h1 class="text-2xl font-semibold tracking-tight">Utilisateurs</h1>
    <AdminGate redirect="/admin/users">
      <form class="mt-6 max-w-md space-y-4" @submit.prevent="onLoad">
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Identifiant</span>
          <input v-model="userId" required class="w-full rounded-md border border-stone-300 px-3 py-2" />
        </label>
        <button
          type="submit"
          class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800 disabled:opacity-50"
          :disabled="pending"
        >
          Charger
        </button>
      </form>
      <p v-if="errorMessage" class="mt-4" role="alert">{{ errorMessage }}</p>
      <p v-if="successMessage" class="mt-4" role="status">{{ successMessage }}</p>
      <form v-if="user" class="mt-6 max-w-md space-y-4" @submit.prevent="onSave">
        <p>{{ user.email }}</p>
        <label class="block text-sm">
          <span class="mb-1 block text-stone-600">Rôle</span>
          <select v-model="role" class="w-full rounded-md border border-stone-300 px-3 py-2">
            <option value="customer">Client</option>
            <option value="admin">Administrateur</option>
          </select>
        </label>
        <button
          type="submit"
          class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800 disabled:opacity-50"
          :disabled="pending"
        >
          Enregistrer
        </button>
      </form>
    </AdminGate>
  </section>
</template>
