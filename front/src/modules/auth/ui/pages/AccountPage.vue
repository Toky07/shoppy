<script setup lang="ts">
import { computed, inject } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import PageHeader from '@/shared/ui/PageHeader.vue'
import { authSessionKey } from '../../application/authSessionKey'
import AddressBookPanel from '@/modules/account/ui/components/AddressBookPanel.vue'
import AccountActivityPanel from '../components/AccountActivityPanel.vue'
import AccountIdentityCard from '../components/AccountIdentityCard.vue'
import AccountPreferencesPanel from '../components/AccountPreferencesPanel.vue'
import AccountSecurityPanel from '../components/AccountSecurityPanel.vue'
import AuthRequiredPanel from '../components/AuthRequiredPanel.vue'

const { t } = useI18n()
const session = inject(authSessionKey)
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
</script>

<template>
  <section class="animate-fade-in">
    <PageHeader
      :eyebrow="t('account.eyebrow')"
      :title="t('account.pageTitle')"
      icon="user"
      :description="t('account.pageDescription')"
    />

    <AuthRequiredPanel
      v-if="!isAuthenticated"
      :message="t('account.authRequired')"
      redirect="/account"
    />

    <div v-else class="mt-10 grid items-start gap-6">
      <div class="grid items-start gap-6 lg:grid-cols-[1.15fr_1fr]">
        <AccountIdentityCard />
        <div class="grid gap-6">
          <AccountActivityPanel />
          <AccountPreferencesPanel />
        </div>
      </div>
      <AddressBookPanel />
      <AccountSecurityPanel />
    </div>
  </section>
</template>
