<script setup lang="ts">
import { computed, inject } from 'vue'
import PageHeader from '@/shared/ui/PageHeader.vue'
import { authSessionKey } from '../application/authSessionKey'
import AccountActivityPanel from './AccountActivityPanel.vue'
import AccountIdentityCard from './AccountIdentityCard.vue'
import AccountPreferencesPanel from './AccountPreferencesPanel.vue'
import AuthRequiredPanel from './AuthRequiredPanel.vue'

const session = inject(authSessionKey)
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
</script>

<template>
  <section class="animate-fade-in">
    <PageHeader
      eyebrow="Compte"
      title="Mon profil"
      icon="user"
      description="Vos informations, vos raccourcis et vos préférences d'affichage."
    />

    <AuthRequiredPanel
      v-if="!isAuthenticated"
      message="Connectez-vous pour consulter votre profil."
      redirect="/account"
    />

    <div v-else class="mt-10 grid items-start gap-6 lg:grid-cols-[1.15fr_1fr]">
      <AccountIdentityCard />
      <div class="grid gap-6">
        <AccountActivityPanel />
        <AccountPreferencesPanel />
      </div>
    </div>
  </section>
</template>
