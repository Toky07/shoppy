<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AdminGate from './AdminGate.vue'
</script>

<template>
  <section class="animate-fade-in">
    <div class="mb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900">Administration</h1>
      <p class="text-gray-500 mt-2">Gérez votre boutique, vos commandes et vos utilisateurs.</p>
    </div>

    <AdminGate redirect="/admin">
      <div class="grid md:grid-cols-3 gap-6 mt-8">
        <RouterLink to="/admin/orders" aria-label="Commandes" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all flex flex-col items-center text-center">
          <div class="h-16 w-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-box-open text-2xl"></i>
          </div>
          <h2 class="text-lg font-bold text-gray-900 mb-1">Commandes</h2>
          <p class="text-sm text-gray-500">Gérez les commandes clients et les paiements</p>
        </RouterLink>

        <RouterLink to="/admin/products" aria-label="Catalogue" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:indigo-100 transition-all flex flex-col items-center text-center">
          <div class="h-16 w-16 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-tags text-2xl"></i>
          </div>
          <h2 class="text-lg font-bold text-gray-900 mb-1">Catalogue</h2>
          <p class="text-sm text-gray-500">Gérez vos produits, prix et stocks</p>
        </RouterLink>

        <RouterLink to="/admin/users" aria-label="Utilisateurs" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all flex flex-col items-center text-center">
          <div class="h-16 w-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-users text-2xl"></i>
          </div>
          <h2 class="text-lg font-bold text-gray-900 mb-1">Utilisateurs</h2>
          <p class="text-sm text-gray-500">Gérez les comptes clients et administrateurs</p>
        </RouterLink>
      </div>
    </AdminGate>
  </section>
</template>
