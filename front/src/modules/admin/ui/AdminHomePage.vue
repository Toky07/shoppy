<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import type { IconName } from '@/shared/ui/icons'
import AdminGate from './AdminGate.vue'
import AdminPageHeader from './AdminPageHeader.vue'

const sections: { to: string; label: string; icon: IconName; description: string }[] = [
  {
    to: '/admin/orders',
    label: 'Commandes',
    icon: 'package',
    description: 'Suivre les commandes clients et confirmer les paiements.',
  },
  {
    to: '/admin/products',
    label: 'Catalogue',
    icon: 'tag',
    description: 'Créer, modifier et retirer des produits, ajuster les stocks.',
  },
  {
    to: '/admin/users',
    label: 'Utilisateurs',
    icon: 'users',
    description: 'Consulter les comptes et attribuer les rôles.',
  },
]
</script>

<template>
  <section class="animate-fade-in">
    <AdminPageHeader
      eyebrow="Console"
      title="Administration"
      icon="settings"
      description="Le back-office de la boutique : commandes, catalogue et accès."
    />

    <AdminGate redirect="/admin">
      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <RouterLink
          v-for="section in sections"
          :key="section.to"
          :to="section.to"
          :aria-label="section.label"
          class="group panel relative flex flex-col p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lifted"
        >
          <span
            class="flex size-12 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong transition-colors group-hover:bg-accent-soft"
          >
            <AppIcon :name="section.icon" :size="22" />
          </span>
          <h2 class="mt-5 font-display text-lg font-bold text-strong">{{ section.label }}</h2>
          <p class="mt-2 text-sm leading-relaxed text-muted">{{ section.description }}</p>
          <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-strong">
            Ouvrir
            <AppIcon
              name="arrow-right"
              :size="15"
              class="transition-transform duration-300 group-hover:translate-x-1"
            />
          </span>
        </RouterLink>
      </div>
    </AdminGate>
  </section>
</template>
