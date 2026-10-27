<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink } from 'vue-router'
import { legalDocumentsFor } from '@/modules/legal/data/legalDocuments'
import NewsletterForm from '@/modules/newsletter/ui/components/NewsletterForm.vue'
import { cookieConsentKey } from '@/shared/consent/cookieConsentKey'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from './AppIcon.vue'
import BrandMark from './BrandMark.vue'

const consent = inject(cookieConsentKey)

if (!consent) {
  throw new Error('CookieConsent is not provided.')
}

const { locale, t } = useI18n()
const pages = computed(() => legalDocumentsFor(locale.value))
const year = new Date().getFullYear()
</script>

<template>
  <footer class="mt-10 border-t border-line bg-surface">
    <div class="mx-auto max-w-7xl px-5 py-14 lg:px-6">
      <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
          <div class="flex items-center gap-2.5">
            <span class="text-strong"><BrandMark :size="30" /></span>
            <span class="display-tight text-xl text-strong">Shoppy</span>
          </div>
          <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">
            {{ t('footer.tagline') }}
          </p>
          <NewsletterForm />
        </div>

        <div>
          <p class="field-label">{{ t('footer.shop') }}</p>
          <ul class="space-y-3 text-sm">
            <li><RouterLink to="/" class="link-quiet">{{ t('footer.allProducts') }}</RouterLink></li>
            <li><RouterLink to="/favorites" class="link-quiet">{{ t('footer.wishlist') }}</RouterLink></li>
            <li><RouterLink to="/cart" class="link-quiet">{{ t('footer.cart') }}</RouterLink></li>
            <li><RouterLink to="/orders" class="link-quiet">{{ t('footer.orders') }}</RouterLink></li>
          </ul>
        </div>

        <div>
          <p class="field-label">{{ t('footer.help') }}</p>
          <ul class="space-y-3 text-sm">
            <li><RouterLink to="/retours" class="link-quiet">{{ t('footer.shipping') }}</RouterLink></li>
            <li><span class="link-quiet">{{ t('footer.payment') }}</span></li>
            <li><span class="link-quiet">{{ t('footer.contact') }}</span></li>
            <li><span class="link-quiet">{{ t('footer.faq') }}</span></li>
          </ul>
        </div>

        <div>
          <p class="field-label">{{ t('footer.follow') }}</p>
          <ul class="space-y-3 text-sm">
            <li><span class="link-quiet">Instagram</span></li>
            <li><span class="link-quiet">LinkedIn</span></li>
            <li><span class="link-quiet">{{ t('footer.journal') }}</span></li>
          </ul>
        </div>
      </div>

      <div class="mt-12 border-t border-line pt-8">
        <nav
          class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs sm:justify-start"
          :aria-label="t('footer.legal')"
        >
          <RouterLink
            v-for="page in pages"
            :key="page.slug"
            :to="page.path"
            class="link-quiet"
          >
            {{ page.navLabel }}
          </RouterLink>
          <button type="button" class="link-quiet" @click="consent.review">{{ t('footer.cookies') }}</button>
        </nav>
        <div class="mt-6 flex flex-col-reverse items-center justify-between gap-6 sm:flex-row">
          <p class="text-xs text-faint">{{ t('footer.copyright', { year }) }}</p>
          <div class="flex items-center gap-2 text-faint">
            <span class="badge-neutral"><AppIcon name="credit-card" :size="13" /> Visa</span>
            <span class="badge-neutral">Mastercard</span>
            <span class="badge-neutral"><AppIcon name="lock" :size="13" /> Stripe</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
