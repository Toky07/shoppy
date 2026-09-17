<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from './AppIcon.vue'
import BrandMark from './BrandMark.vue'

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
            Une sélection courte d'objets bien faits. Expédiés vite, payés en toute sécurité.
          </p>
          <form class="mt-6 max-w-xs" @submit.prevent>
            <label class="field-label" for="newsletter">Lettre d'information</label>
            <div class="flex gap-2">
              <input
                id="newsletter"
                type="email"
                placeholder="vous@domaine.com"
                class="field rounded-full py-2.5 text-sm"
              />
              <button type="submit" class="btn-accent shrink-0" aria-label="S'abonner">
                <AppIcon name="arrow-right" :size="17" />
              </button>
            </div>
          </form>
        </div>

        <div>
          <p class="field-label">Boutique</p>
          <ul class="space-y-3 text-sm">
            <li><RouterLink to="/" class="link-quiet">Tous les produits</RouterLink></li>
            <li><RouterLink to="/favorites" class="link-quiet">Ma liste d'envies</RouterLink></li>
            <li><RouterLink to="/cart" class="link-quiet">Mon panier</RouterLink></li>
            <li><RouterLink to="/orders" class="link-quiet">Mes achats</RouterLink></li>
          </ul>
        </div>

        <div>
          <p class="field-label">Aide</p>
          <ul class="space-y-3 text-sm">
            <li><span class="link-quiet">Livraison &amp; retours</span></li>
            <li><span class="link-quiet">Paiement sécurisé</span></li>
            <li><span class="link-quiet">Nous écrire</span></li>
            <li><span class="link-quiet">Questions fréquentes</span></li>
          </ul>
        </div>

        <div>
          <p class="field-label">Suivez-nous</p>
          <ul class="space-y-3 text-sm">
            <li><span class="link-quiet">Instagram</span></li>
            <li><span class="link-quiet">LinkedIn</span></li>
            <li><span class="link-quiet">Journal</span></li>
          </ul>
        </div>
      </div>

      <div
        class="mt-12 flex flex-col-reverse items-center justify-between gap-6 border-t border-line pt-8 sm:flex-row"
      >
        <p class="text-xs text-faint">© {{ year }} Shoppy · Conçu et développé avec soin.</p>
        <div class="flex items-center gap-2 text-faint">
          <span class="badge-neutral"><AppIcon name="credit-card" :size="13" /> Visa</span>
          <span class="badge-neutral">Mastercard</span>
          <span class="badge-neutral"><AppIcon name="lock" :size="13" /> Stripe</span>
        </div>
      </div>
    </div>
  </footer>
</template>
