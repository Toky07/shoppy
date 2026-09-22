<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { useCopyToClipboard } from '@/shared/clipboard/useCopyToClipboard'
import { formatDate } from '@/shared/datetime/formatDate'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { logout } from '../application/logout'
import { userInitials } from '@/shared/text/userInitials'
import { userRoleLabel } from './userRoleLabel'

const session = inject(authSessionKey)
const repository = inject(authRepositoryKey)
const router = useRouter()
const { copied, copy } = useCopyToClipboard()
const pending = ref(false)

const user = computed(() => session?.session.value?.user ?? null)
const initials = computed(() => (user.value ? userInitials(user.value.email) : ''))

async function onCopyId() {
  if (!user.value) {
    return
  }

  await copy(user.value.id)
}

async function onLogout() {
  if (!repository || !session) {
    return
  }

  pending.value = true
  await logout(repository, session)
  pending.value = false
  await router.push('/')
}
</script>

<template>
  <div v-if="user" class="panel overflow-hidden">
    <div class="mesh grain flex items-center gap-4 p-6 sm:p-7">
      <span
        class="relative z-1 flex size-16 shrink-0 items-center justify-center rounded-2xl bg-primary font-display text-xl font-extrabold text-primary-fg shadow-lifted"
        aria-hidden="true"
        >{{ initials }}</span
      >
      <span class="relative z-1 min-w-0">
        <span class="block truncate font-display text-lg font-bold text-strong">
          {{ user.email }}
        </span>
        <span class="mt-2 flex flex-wrap items-center gap-2">
          <span :class="user.role === 'admin' ? 'badge-accent' : 'badge-neutral'">
            <AppIcon :name="user.role === 'admin' ? 'shield' : 'user'" :size="12" />
            {{ userRoleLabel(user.role) }}
          </span>
          <span v-if="!user.emailVerified" class="badge-accent">Email à confirmer</span>
        </span>
      </span>
    </div>

    <dl class="divide-y divide-line">
      <div class="flex items-center justify-between gap-4 px-6 py-4 sm:px-7">
        <dt class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">Membre depuis</dt>
        <dd class="text-sm font-medium text-strong">{{ formatDate(user.createdAt) }}</dd>
      </div>
      <div class="flex items-center justify-between gap-4 px-6 py-4 sm:px-7">
        <dt class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">Identifiant</dt>
        <dd class="flex min-w-0 items-center gap-2">
          <span class="numeric truncate text-xs text-faint">{{ user.id }}</span>
          <button
            type="button"
            class="btn-icon size-8 shrink-0"
            :aria-label="copied ? 'Identifiant copié' : 'Copier mon identifiant'"
            :title="copied ? 'Identifiant copié' : 'Copier mon identifiant'"
            @click="onCopyId"
          >
            <AppIcon :name="copied ? 'check' : 'copy'" :size="15" />
          </button>
        </dd>
      </div>
    </dl>

    <div class="border-t border-line bg-surface-inset px-6 py-5 sm:px-7">
      <p class="flex items-center gap-2 text-xs text-muted">
        <AppIcon name="shield" :size="15" />
        Session active sur cet appareil uniquement.
      </p>
      <button type="button" class="btn-danger mt-4" :disabled="pending" @click="onLogout">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="15" /></span>
        <AppIcon v-else name="logout" :size="15" />
        Se déconnecter
      </button>
    </div>
  </div>
</template>
