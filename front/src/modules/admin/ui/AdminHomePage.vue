<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import type { IconName } from '@/shared/ui/icons'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'

const session = inject(authSessionKey)
const email = computed(() => session?.session.value?.user.email ?? '')

const sections: { to: string; label: string; icon: IconName; description: string; action: string }[] = [
  {
    to: '/admin/orders',
    label: 'Commandes',
    icon: 'package',
    description: 'Suivre les achats, confirmer les paiements et consulter le détail.',
    action: 'Ouvrir les commandes',
  },
  {
    to: '/admin/products',
    label: 'Catalogue',
    icon: 'tag',
    description: 'Créer, modifier et retirer des produits, ajuster les stocks.',
    action: 'Gérer le catalogue',
  },
  {
    to: '/admin/users',
    label: 'Utilisateurs',
    icon: 'users',
    description: 'Consulter un compte et attribuer un rôle client ou administrateur.',
    action: 'Gérer les accès',
  },
]
</script>

<template>
  <section class="animate-fade-in">
    <div
      class="relative overflow-hidden rounded-2xl border border-line bg-surface px-6 py-8 sm:px-8"
    >
      <div class="pointer-events-none absolute inset-0 mesh opacity-70"></div>
      <div class="relative">
        <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">
          Vue d'ensemble
        </p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-strong">
          Tableau de bord
        </h1>
        <p class="mt-2 max-w-lg text-sm text-muted">
          Bienvenue{{ email ? `, ${email}` : '' }}. La console est séparée de la boutique : ici, vous
          pilotez le catalogue, les commandes et les accès.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
          <RouterLink to="/admin/products/new" class="btn-primary">
            <AppIcon name="plus" :size="16" />
            Nouveau produit
          </RouterLink>
          <RouterLink to="/" class="btn-outline">
            <AppIcon name="globe" :size="16" />
            Voir la boutique
          </RouterLink>
        </div>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-3">
      <RouterLink
        v-for="section in sections"
        :key="section.to"
        :to="section.to"
        :aria-label="section.label"
        class="group panel relative flex flex-col p-5 transition-colors hover:border-line-strong hover:bg-surface-muted"
      >
        <span
          class="flex size-11 items-center justify-center rounded-xl border border-line bg-surface-inset text-accent-strong transition-colors group-hover:bg-accent-soft"
        >
          <AppIcon :name="section.icon" :size="20" />
        </span>
        <h2 class="mt-4 font-display text-lg font-bold text-strong">{{ section.label }}</h2>
        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-muted">{{ section.description }}</p>
        <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-strong">
          {{ section.action }}
          <AppIcon
            name="arrow-right"
            :size="15"
            class="transition-transform duration-200 group-hover:translate-x-1"
          />
        </span>
      </RouterLink>
    </div>
  </section>
</template>
