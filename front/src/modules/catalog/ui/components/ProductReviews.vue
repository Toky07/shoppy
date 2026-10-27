<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { catalogRepositoryKey } from '../../application/catalogRepositoryKey'
import type { ProductReviewList } from '../../domain/ProductReview'
import { formatDate } from '@/shared/datetime/formatDate'
import { toApiError } from '@/shared/http/toApiError'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'

const props = defineProps<{
  productId: string
  productSlug: string
}>()

const repository = inject(catalogRepositoryKey)
const session = inject(authSessionKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const catalog = repository
const { t, intlLocale } = useI18n()
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const reviews = ref<ProductReviewList>({ items: [], count: 0, averageRating: null })
const rating = ref(5)
const body = ref('')
const loadError = ref<string>()
const reportedIds = ref<string[]>([])
const reportingId = ref<string>()
const reportError = ref<string>()
const { pending, errorMessage, run } = usePendingAction(
  (error) => error.violations[0]?.message ?? error.message,
)

const summary = computed(() => {
  if (reviews.value.count === 0 || reviews.value.averageRating === null) {
    return t('reviews.none')
  }
  const note = reviews.value.averageRating.toLocaleString(intlLocale.value, {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
  })
  return t('reviews.summary', { note, count: reviews.value.count })
})

const ownReview = computed(() => reviews.value.items.find((review) => review.mine) ?? null)

async function load() {
  loadError.value = undefined
  try {
    reviews.value = await catalog.listReviews(props.productId)
    if (ownReview.value) {
      rating.value = ownReview.value.rating
      body.value = ownReview.value.body
    }
  } catch (caught) {
    loadError.value = toApiError(caught).message
  }
}

watch(() => props.productId, load, { immediate: true })

function onSubmit() {
  return run(async () => {
    await catalog.submitReview(props.productId, {
      rating: rating.value,
      body: body.value.trim(),
    })
    await load()
  })
}

async function onReport(reviewId: string) {
  reportingId.value = reviewId
  reportError.value = undefined
  try {
    await catalog.reportReview(props.productId, reviewId)
    reportedIds.value = [...reportedIds.value, reviewId]
  } catch (caught) {
    reportError.value = toApiError(caught).message
  } finally {
    reportingId.value = undefined
  }
}

function isReported(reviewId: string): boolean {
  return reportedIds.value.includes(reviewId)
}
</script>

<template>
  <section class="mt-16 border-t border-line pt-12" aria-labelledby="product-reviews-title">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-[0.7rem] font-semibold tracking-[0.16em] text-muted uppercase">{{ t('reviews.eyebrow') }}</p>
        <h2 id="product-reviews-title" class="mt-2 font-display text-3xl font-extrabold text-strong">{{ t('reviews.title') }}</h2>
      </div>
      <p class="inline-flex items-center gap-2 text-sm font-semibold text-strong">
        <AppIcon name="star" :size="16" filled />
        {{ summary }}
      </p>
    </div>

    <StatusNotice v-if="loadError" tone="danger" class="mt-6">{{ loadError }}</StatusNotice>
    <StatusNotice v-if="reportError" tone="danger" class="mt-6">{{ reportError }}</StatusNotice>

    <ul v-if="reviews.items.length > 0" class="mt-8 space-y-4">
      <li v-for="review in reviews.items" :key="review.id" class="panel p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <p class="font-semibold text-strong">{{ review.author }}</p>
          <p class="text-sm text-muted">
            <span class="font-semibold text-strong">{{ review.rating }}/5</span>
            · {{ formatDate(review.createdAt, intlLocale) }}
          </p>
        </div>
        <p class="mt-3 text-sm leading-relaxed text-body">{{ review.body }}</p>
        <div v-if="isAuthenticated && !review.mine" class="mt-4">
          <p v-if="isReported(review.id)" class="text-xs font-semibold text-muted">{{ t('reviews.reported') }}</p>
          <button
            v-else
            type="button"
            class="text-xs font-semibold link-quiet"
            :disabled="reportingId === review.id"
            @click="onReport(review.id)"
          >
            {{ t('reviews.report') }}
          </button>
        </div>
      </li>
    </ul>
    <p v-else-if="!loadError" class="mt-8 text-sm text-faint italic">{{ t('reviews.noneYet') }}</p>

    <form v-if="isAuthenticated" class="panel mt-8 space-y-4 p-5 sm:p-6" @submit.prevent="onSubmit">
      <p class="font-display text-lg font-bold text-strong">
        {{ ownReview ? t('reviews.editYours') : t('reviews.giveYours') }}
      </p>
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
      <div>
        <p class="field-label">{{ t('reviews.rating') }}</p>
        <div class="mt-2 flex gap-2" role="radiogroup" :aria-label="t('reviews.rating')">
          <label v-for="value in [1, 2, 3, 4, 5]" :key="value" class="cursor-pointer">
            <input v-model.number="rating" class="peer sr-only" type="radio" name="rating" :value="value" />
            <span
              class="inline-flex size-10 items-center justify-center rounded-full border border-line text-sm font-semibold peer-checked:border-accent peer-checked:bg-accent peer-checked:text-white"
            >
              {{ value }}
            </span>
          </label>
        </div>
      </div>
      <label class="block">
        <span class="field-label">{{ t('reviews.comment') }}</span>
        <textarea v-model="body" required maxlength="1000" rows="4" class="field mt-2 resize-y" />
      </label>
      <button type="submit" class="btn-primary" :disabled="pending">
        {{ ownReview ? t('reviews.update') : t('reviews.publish') }}
      </button>
    </form>
    <p v-else class="mt-8 text-sm text-muted">
      <RouterLink
        :to="{ path: '/login', query: { redirect: `/products/${productSlug}` } }"
        class="font-semibold link-quiet"
      >
        {{ t('auth.signInCta') }}
      </RouterLink>
      {{ t('reviews.signInSuffix') }}
    </p>
  </section>
</template>
