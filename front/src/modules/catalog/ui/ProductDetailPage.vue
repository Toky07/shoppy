<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import AddToCartForm from '@/modules/cart/ui/AddToCartForm.vue'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { useProduct } from '../application/useProduct'
import FavoriteButton from './FavoriteButton.vue'
import ProductImage from './ProductImage.vue'
import ProductPrice from './ProductPrice.vue'
import { stockLabel } from './stockLabel'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const route = useRoute()
const productId = computed(() => String(route.params.id ?? ''))
const { status, product, error } = useProduct(repository, productId)
const notFound = computed(() => error.value?.code === 'product_not_found')
const errorMessage = computed(() =>
  notFound.value ? 'Ce produit est introuvable.' : error.value?.message,
)
const outOfStock = computed(() => (product.value?.stock ?? 0) <= 0)

const tabs = [
  { id: 'description', label: 'Description' },
  { id: 'delivery', label: 'Livraison' },
  { id: 'warranty', label: 'Garantie' },
] as const

const activeTab = ref<(typeof tabs)[number]['id']>('description')
const copied = ref(false)

const reassurance = [
  { icon: 'truck', title: 'Expédié sous 48 h', text: 'Suivi inclus, emballage sobre' },
  { icon: 'refresh', title: 'Retour sous 30 jours', text: 'Remboursé sans discuter' },
  { icon: 'shield', title: 'Garantie 2 ans', text: 'Pièces et main d\'œuvre' },
  { icon: 'lock', title: 'Paiement sécurisé', text: 'Carte bancaire via Stripe' },
] as const

async function onCopyLink() {
  try {
    await navigator.clipboard.writeText(window.location.href)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch {
    /* presse-papier refusé */
  }
}
</script>

<template>
  <section class="animate-fade-in">
    <nav class="mb-8 flex items-center gap-3 text-sm" aria-label="Fil d'Ariane">
      <RouterLink to="/" class="inline-flex items-center gap-2 font-medium link-quiet">
        <AppIcon name="arrow-left" :size="15" />
        Retour au catalogue
      </RouterLink>
      <span v-if="product" class="text-faint" aria-hidden="true">/</span>
      <span v-if="product" class="truncate font-medium text-strong">{{ product.name }}</span>
    </nav>

    <PageStatus
      :status="status === 'ready' ? 'ready' : status"
      :error-message="errorMessage"
      skeleton="detail"
    >
      <article v-if="product" class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
        <!-- Visuel -->
        <div class="lg:sticky lg:top-28">
          <div
            class="group relative aspect-4/5 overflow-hidden rounded-panel border border-line bg-surface shadow-lifted"
          >
            <ProductImage
              :src="product.imageUrl"
              :alt="product.name"
              class="transition-transform duration-[900ms] group-hover:scale-105"
            />
            <div class="absolute top-5 left-5 flex flex-col gap-2">
              <span v-if="outOfStock" class="badge-danger">Rupture</span>
              <span v-else class="badge-accent">Disponible</span>
            </div>
            <div class="absolute top-5 right-5">
              <FavoriteButton
                :product-id="product.id"
                :product-name="product.name"
                size="lg"
              />
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between px-1">
            <p class="text-xs text-faint">Réf. {{ product.id.slice(0, 8).toUpperCase() }}</p>
            <button
              type="button"
              class="inline-flex items-center gap-2 text-xs font-semibold link-quiet"
              @click="onCopyLink"
            >
              <AppIcon :name="copied ? 'check' : 'copy'" :size="14" />
              {{ copied ? 'Lien copié' : 'Partager' }}
            </button>
          </div>
        </div>

        <!-- Achat -->
        <div class="flex flex-col">
          <p class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">
            <span>{{ stockLabel(product.stock) }}</span>
          </p>

          <h1 class="display-tight mt-4 text-4xl text-strong sm:text-5xl">{{ product.name }}</h1>

          <div class="mt-6 flex flex-wrap items-baseline gap-4">
            <p class="numeric font-display text-4xl font-extrabold text-strong">
              <ProductPrice :price="product.price" />
            </p>
            <p class="text-xs text-muted">TTC · livraison offerte dès 49 €</p>
          </div>

          <div class="panel mt-8 p-5 sm:p-6">
            <AddToCartForm :product-id="product.id" :stock="product.stock" />
          </div>

          <!-- Onglets -->
          <div class="mt-10">
            <div class="flex gap-1 border-b border-line" role="tablist" aria-label="Détails du produit">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                role="tab"
                class="relative -mb-px border-b-2 px-4 py-3 text-sm font-semibold transition-colors"
                :class="
                  activeTab === tab.id
                    ? 'border-accent text-strong'
                    : 'border-transparent text-muted hover:text-strong'
                "
                :aria-selected="activeTab === tab.id"
                @click="activeTab = tab.id"
              >
                {{ tab.label }}
              </button>
            </div>

            <div class="pt-6 text-sm leading-relaxed text-body">
              <template v-if="activeTab === 'description'">
                <p v-if="product.description">{{ product.description }}</p>
                <p v-else class="text-faint italic">Aucune description disponible pour ce produit.</p>
              </template>
              <p v-else-if="activeTab === 'delivery'">
                Expédition depuis la France sous 48 heures ouvrées, avec numéro de suivi. Livraison
                offerte à partir de 49 € d'achat, retours gratuits pendant 30 jours.
              </p>
              <p v-else>
                Deux ans de garantie constructeur sur les pièces et la main d'œuvre. En cas de
                souci, on remplace ou on rembourse, sans formulaire à rallonge.
              </p>
            </div>
          </div>

          <!-- Réassurance -->
          <ul class="mt-10 grid gap-4 sm:grid-cols-2">
            <li v-for="item in reassurance" :key="item.title" class="flex items-start gap-3">
              <span
                class="flex size-10 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
              >
                <AppIcon :name="item.icon" :size="18" />
              </span>
              <span>
                <strong class="block text-sm font-semibold text-strong">{{ item.title }}</strong>
                <span class="text-xs text-muted">{{ item.text }}</span>
              </span>
            </li>
          </ul>
        </div>
      </article>
    </PageStatus>
  </section>
</template>
