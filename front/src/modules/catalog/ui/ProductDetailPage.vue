<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { useProduct } from '../application/useProduct'
import ProductPrice from './ProductPrice.vue'
import ProductImage from './ProductImage.vue'
import AddToCartForm from '@/modules/cart/ui/AddToCartForm.vue'
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
</script>

<template>
  <section class="animate-fade-in pb-12">
    <!-- Breadcrumb -->
    <nav class="mb-8 flex items-center text-sm font-medium text-gray-500">
      <RouterLink to="/" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
      </RouterLink>
      <span class="mx-3 text-gray-300">/</span>
      <span class="text-gray-900 line-clamp-1" v-if="product">{{ product.name }}</span>
    </nav>

    <PageStatus :status="status === 'ready' ? 'ready' : status" :error-message="errorMessage">
      <article v-if="product" class="grid gap-12 lg:gap-20 lg:grid-cols-2 items-start">
        
        <!-- Image Section (Sticky) -->
        <div class="lg:sticky lg:top-28 relative rounded-[2.5rem] overflow-hidden bg-white shadow-2xl shadow-gray-200/50 aspect-[4/5] flex items-center justify-center group">
          <ProductImage :src="product.imageUrl" :alt="product.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
          
          <!-- Floating Badges -->
          <div class="absolute top-6 left-6 flex flex-col gap-3">
            <span class="px-4 py-1.5 bg-white/90 backdrop-blur-md text-gray-900 text-xs font-black uppercase tracking-widest rounded-full shadow-lg">Nouveau</span>
          </div>
          
          <!-- Wishlist -->
          <div class="absolute top-6 right-6">
            <button class="h-14 w-14 rounded-full bg-white/90 backdrop-blur-md text-gray-400 hover:text-red-500 shadow-lg flex items-center justify-center transition-all hover:scale-110">
              <i class="fa-regular fa-heart text-2xl"></i>
            </button>
          </div>
        </div>

        <!-- Details Section -->
        <div class="flex flex-col pt-4 lg:pt-10">
          <!-- Status & Title -->
          <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
              <span class="flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 text-xs font-bold uppercase tracking-wider rounded-md">
                <i class="fa-solid fa-check"></i> En stock
              </span>
              <span class="text-sm font-medium text-gray-500 flex items-center gap-1.5">
                <i class="fa-solid fa-box"></i> {{ stockLabel(product.stock) }}
              </span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight mb-4">
              {{ product.name }}
            </h1>
            
            <div class="flex items-baseline gap-4">
              <p class="text-4xl font-black text-indigo-600">
                <ProductPrice :price="product.price" />
              </p>
            </div>
          </div>

          <!-- Description -->
          <div class="prose prose-gray prose-lg mb-10">
            <p v-if="product.description" class="text-gray-600 leading-relaxed">
              {{ product.description }}
            </p>
            <p v-else class="text-gray-400 italic">Aucune description disponible pour ce produit.</p>
          </div>

          <!-- Action Area -->
          <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 mb-10">
            <AddToCartForm :product-id="product.id" :stock="product.stock" class="w-full" />
          </div>
          
          <!-- Trust Badges -->
          <div class="grid grid-cols-2 gap-y-8 gap-x-4">
            <div class="flex items-start gap-4">
              <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-truck-fast text-xl"></i>
              </div>
              <div>
                <h4 class="text-sm font-bold text-gray-900">Livraison Express</h4>
                <p class="text-xs text-gray-500 mt-1">Sous 24/48h chez vous</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-rotate-left text-xl"></i>
              </div>
              <div>
                <h4 class="text-sm font-bold text-gray-900">Retours 30 jours</h4>
                <p class="text-xs text-gray-500 mt-1">Satisfait ou remboursé</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-shield-halved text-xl"></i>
              </div>
              <div>
                <h4 class="text-sm font-bold text-gray-900">Garantie 2 ans</h4>
                <p class="text-xs text-gray-500 mt-1">Sur tous nos produits</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-lock text-xl"></i>
              </div>
              <div>
                <h4 class="text-sm font-bold text-gray-900">Paiement 100% sûr</h4>
                <p class="text-xs text-gray-500 mt-1">Données cryptées</p>
              </div>
            </div>
          </div>
        </div>
      </article>
    </PageStatus>
  </section>
</template>
