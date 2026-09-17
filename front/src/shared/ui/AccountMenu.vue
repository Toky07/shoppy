<script setup lang="ts">
import { computed, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from './AppIcon.vue'
import ThemeToggle from './ThemeToggle.vue'
import { usePopupMenu } from './usePopupMenu'
import type { IconName } from './icons'
import { userInitials } from '@/shared/text/userInitials'

const props = defineProps<{
  email: string
  roleLabel: string
  isAdmin: boolean
  favoriteCount: number
  cartCount: number
}>()

const emit = defineEmits<{
  logout: []
}>()

const route = useRoute()
const { open, triggerEl, menuEl, close, onTriggerKeydown, onMenuKeydown } = usePopupMenu()
const initials = computed(() => userInitials(props.email))

type Entry = { to: string; label: string; icon: IconName; count?: number }

const entries = computed<Entry[]>(() => [
  { to: '/account', label: 'Mon profil', icon: 'user' },
  { to: '/favorites', label: 'Mes favoris', icon: 'heart', count: props.favoriteCount },
  { to: '/orders', label: 'Mes commandes', icon: 'package' },
  { to: '/cart', label: 'Mon panier', icon: 'cart', count: props.cartCount },
  ...(props.isAdmin
    ? ([{ to: '/admin', label: 'Administration', icon: 'settings' }] as Entry[])
    : []),
])

watch(
  () => route.fullPath,
  () => close(),
)

function onLogout() {
  close()
  emit('logout')
}
</script>

<template>
  <div class="relative">
    <button
      ref="triggerEl"
      type="button"
      class="flex items-center gap-2 rounded-full border py-1 pr-2.5 pl-1 transition-all duration-200"
      :class="
        open
          ? 'border-strong bg-surface-muted shadow-soft'
          : 'border-line bg-surface hover:border-line-strong hover:bg-surface-muted'
      "
      aria-haspopup="menu"
      :aria-expanded="open"
      @click="open = !open"
      @keydown="onTriggerKeydown"
    >
      <span class="sr-only">Mon compte : </span>
      <span
        class="flex size-7 items-center justify-center rounded-full bg-accent-soft text-[0.65rem] font-bold text-accent-fg"
        aria-hidden="true"
        >{{ initials }}</span
      >
      <span class="max-w-40 truncate text-xs font-medium text-body">{{ email }}</span>
      <AppIcon
        name="chevron-down"
        :size="14"
        class="text-faint transition-transform duration-300"
        :class="open && 'rotate-180'"
      />
    </button>

    <Transition
      enter-active-class="transition duration-200 ease-[cubic-bezier(0.22,1,0.36,1)]"
      leave-active-class="transition duration-150 ease-in"
      enter-from-class="opacity-0 -translate-y-2 scale-95"
      leave-to-class="opacity-0 -translate-y-1 scale-[0.98]"
    >
      <div
        v-if="open"
        class="panel absolute right-0 z-60 mt-2.5 w-72 origin-top-right overflow-hidden p-2 shadow-float"
      >
        <div class="mesh flex items-center gap-3 rounded-2xl px-3 py-3.5">
          <span
            class="relative z-1 flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-fg"
            aria-hidden="true"
            >{{ initials }}</span
          >
          <span class="relative z-1 min-w-0">
            <span class="block truncate text-sm font-semibold text-strong">{{ email }}</span>
            <span
              class="mt-0.5 block text-[0.7rem] font-semibold tracking-[0.1em] text-muted uppercase"
            >
              {{ roleLabel }}
            </span>
          </span>
        </div>

        <div
          ref="menuEl"
          role="menu"
          aria-label="Mon compte"
          class="mt-2 grid gap-0.5"
          @keydown="onMenuKeydown"
        >
          <RouterLink
            v-for="(entry, index) in entries"
            :key="entry.to"
            :to="entry.to"
            role="menuitem"
            class="animate-menu-in group flex items-center gap-3 rounded-2xl px-2.5 py-2.5 transition-colors hover:bg-surface-muted focus-visible:bg-surface-muted"
            :style="{ animationDelay: `${index * 30}ms` }"
            @click="close()"
          >
            <span
              class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-line bg-surface-inset text-body transition-colors group-hover:border-line-strong group-hover:text-accent-strong"
            >
              <AppIcon :name="entry.icon" :size="16" />
            </span>
            <span class="flex-1 text-sm font-semibold text-strong">{{ entry.label }}</span>
            <span v-if="entry.count" class="badge-accent" aria-hidden="true">{{ entry.count }}</span>
            <AppIcon
              v-else
              name="chevron-right"
              :size="14"
              class="text-faint opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100"
            />
          </RouterLink>

          <div class="my-1 h-px bg-line" role="none"></div>

          <button
            type="button"
            role="menuitem"
            class="animate-menu-in group flex items-center gap-3 rounded-2xl px-2.5 py-2.5 text-left transition-colors hover:bg-danger-soft focus-visible:bg-danger-soft"
            :style="{ animationDelay: `${entries.length * 30}ms` }"
            @click="onLogout"
          >
            <span
              class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-line bg-surface-inset text-body transition-colors group-hover:border-danger/30 group-hover:text-danger"
            >
              <AppIcon name="logout" :size="16" />
            </span>
            <span
              class="text-sm font-semibold text-strong transition-colors group-hover:text-danger"
            >
              Déconnexion
            </span>
          </button>
        </div>

        <div
          class="mt-2 flex items-center justify-between gap-3 rounded-2xl bg-surface-inset px-3 py-2.5"
        >
          <span class="text-[0.7rem] font-semibold tracking-[0.1em] text-muted uppercase">
            Apparence
          </span>
          <ThemeToggle />
        </div>
      </div>
    </Transition>
  </div>
</template>
