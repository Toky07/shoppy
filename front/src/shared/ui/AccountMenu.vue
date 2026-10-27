<script setup lang="ts">
import { computed, inject, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { cartStateKey } from '@/modules/cart/application/cartStateKey'
import { useFavorites } from '@/modules/catalog/application/useFavorites'
import { userInitials } from '@/shared/text/userInitials'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from './AppIcon.vue'
import ThemeToggle from './ThemeToggle.vue'
import { usePopupMenu } from './usePopupMenu'
import type { IconName } from './icons'

const props = defineProps<{
  email: string
  roleLabel: string
  isAdmin: boolean
}>()

const emit = defineEmits<{
  logout: []
}>()

const { t } = useI18n()
const route = useRoute()
const cartState = inject(cartStateKey)
const { count: favoriteCount } = useFavorites()
const { open, triggerEl, menuEl, close, onTriggerKeydown, onMenuKeydown } = usePopupMenu()
const initials = computed(() => userInitials(props.email))
const cartCount = computed(() => cartState?.itemCount.value ?? 0)

type Entry = { to: string; label: string; icon: IconName; count?: number }

const entries = computed<Entry[]>(() => [
  { to: '/account', label: t('account.profile'), icon: 'user' },
  { to: '/favorites', label: t('account.favorites'), icon: 'heart', count: favoriteCount.value },
  { to: '/orders', label: t('account.orders'), icon: 'package' },
  { to: '/cart', label: t('account.cart'), icon: 'cart', count: cartCount.value },
  ...(props.isAdmin
    ? ([{ to: '/admin', label: t('account.admin'), icon: 'settings' }] as Entry[])
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
      class="relative flex size-10 items-center justify-center rounded-full transition-all duration-200"
      :class="
        open
          ? 'bg-surface-muted ring-2 ring-accent ring-offset-2 ring-offset-canvas'
          : 'hover:bg-surface-muted'
      "
      aria-haspopup="menu"
      :aria-label="t('account.menu')"
      :aria-expanded="open"
      @click="open = !open"
      @keydown="onTriggerKeydown"
    >
      <span
        class="flex size-8 items-center justify-center rounded-full bg-primary font-display text-[0.65rem] font-bold tracking-wide text-primary-fg shadow-soft"
        aria-hidden="true"
        >{{ initials }}</span
      >
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
            class="relative z-1 flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-display text-xs font-bold tracking-wide text-primary-fg"
            aria-hidden="true"
            >{{ initials }}</span
          >
          <span class="relative z-1 min-w-0">
            <span class="block text-[0.65rem] font-semibold tracking-[0.14em] text-muted uppercase">
              {{ roleLabel }}
            </span>
            <span class="mt-0.5 block truncate text-sm font-semibold text-strong">{{ email }}</span>
          </span>
        </div>

        <div
          ref="menuEl"
          role="menu"
          :aria-label="t('account.menu')"
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
              {{ t('account.logout') }}
            </span>
          </button>
        </div>

        <div
          class="mt-2 flex items-center justify-between gap-3 rounded-2xl bg-surface-inset px-3 py-2.5"
        >
          <span class="text-[0.7rem] font-semibold tracking-[0.1em] text-muted uppercase">
            {{ t('account.appearance') }}
          </span>
          <ThemeToggle />
        </div>
      </div>
    </Transition>
  </div>
</template>
