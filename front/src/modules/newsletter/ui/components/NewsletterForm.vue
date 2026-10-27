<script setup lang="ts">
import { inject, ref } from 'vue'
import { newsletterRepositoryKey } from '../../application/newsletterRepositoryKey'
import { newsletterErrorMessage } from '../lib/newsletterErrorMessage'
import { toApiError } from '@/shared/http/toApiError'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'

const injected = inject(newsletterRepositoryKey)

if (!injected) {
  throw new Error('NewsletterRepository is not provided.')
}

const repository = injected

const email = ref('')
const submitting = ref(false)
const subscribed = ref(false)
const errorMessage = ref<string>()

async function subscribe() {
  errorMessage.value = undefined
  submitting.value = true

  try {
    await repository.subscribe(email.value.trim())
    subscribed.value = true
    email.value = ''
  } catch (caught) {
    errorMessage.value = newsletterErrorMessage(toApiError(caught))
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <form class="mt-6 max-w-xs" @submit.prevent="subscribe">
    <label class="field-label" for="newsletter">Lettre d'information</label>
    <div class="flex gap-2">
      <input
        id="newsletter"
        v-model="email"
        type="email"
        name="email"
        required
        autocomplete="email"
        placeholder="vous@domaine.com"
        class="field rounded-full py-2.5 text-sm"
        :disabled="submitting || subscribed"
      />
      <button
        type="submit"
        class="btn-accent shrink-0"
        aria-label="S'abonner"
        :disabled="submitting || subscribed"
      >
        <AppIcon name="arrow-right" :size="17" />
      </button>
    </div>
    <StatusNotice v-if="subscribed" tone="positive" class="mt-3">
      Merci, vous êtes inscrit.
    </StatusNotice>
    <StatusNotice v-else-if="errorMessage" tone="danger" class="mt-3">
      {{ errorMessage }}
    </StatusNotice>
  </form>
</template>
