<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import { legalDocumentByPath } from '../../data/legalDocuments'
import { applyPageMeta, resetPageMeta } from '@/shared/seo/applyPageMeta'
import { useI18n } from '@/shared/i18n/useI18n'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageHeader from '@/shared/ui/PageHeader.vue'

const { locale, t } = useI18n()
const route = useRoute()
const page = computed(() => legalDocumentByPath(route.path, locale.value))

watch(
  page,
  (current) => {
    if (!current) {
      resetPageMeta()
      return
    }

    applyPageMeta({
      title: `${current.title} — Shoppy`,
      description: current.description,
      path: current.path,
    })
  },
  { immediate: true },
)

onBeforeUnmount(resetPageMeta)
</script>

<template>
  <article v-if="page" class="mx-auto max-w-3xl animate-fade-in">
    <PageHeader
      :eyebrow="t('legal.eyebrow')"
      :title="page.title"
      icon="info"
      :description="page.description"
    />

    <div class="mt-10 space-y-8">
      <section v-for="section in page.sections" :key="section.heading">
        <h2 class="text-lg font-semibold text-strong">{{ section.heading }}</h2>
        <p
          v-for="(paragraph, index) in section.paragraphs"
          :key="index"
          class="mt-3 text-sm leading-relaxed text-body"
        >
          {{ paragraph }}
        </p>
      </section>
    </div>
  </article>

  <EmptyState
    v-else
    icon="alert-circle"
    :title="t('legal.notFoundTitle')"
    :description="t('legal.notFoundBody')"
    :heading-level="2"
  />
</template>
