<script setup lang="ts">
import { ref } from 'vue'

const props = withDefaults(
  defineProps<{
    title: string
    submitLabel: string
    pending: boolean
    errorMessage?: string
    passwordAutocomplete?: string
  }>(),
  { passwordAutocomplete: 'current-password' },
)

const emit = defineEmits<{
  submit: [credentials: { email: string; password: string }]
}>()

const email = ref('')
const password = ref('')

function onSubmit() {
  emit('submit', { email: email.value.trim(), password: password.value })
}
</script>

<template>
  <div class="animate-fade-in flex flex-col items-center justify-center py-12">
    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">
      <!-- Decorative background elements -->
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
      <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-violet-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

      <div class="relative z-10">
        <div class="flex justify-center mb-8">
          <div class="h-16 w-16 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
            <i class="fa-solid fa-user-lock text-2xl"></i>
          </div>
        </div>

        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 text-center mb-2">{{ title }}</h1>
        <p class="text-gray-500 text-center mb-8 text-sm">Veuillez entrer vos identifiants pour continuer</p>

        <form class="space-y-5" @submit.prevent="onSubmit">
          <div v-if="errorMessage" role="alert" class="flex items-start gap-3 rounded-xl bg-red-50 p-4 text-sm font-medium text-red-800 border border-red-100">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <p>{{ errorMessage }}</p>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1.5">Adresse email</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-envelope"></i>
              </div>
              <input
                v-model="email"
                type="email"
                name="email"
                autocomplete="email"
                required
                placeholder="vous@exemple.com"
                class="w-full rounded-xl border border-gray-200 pl-11 pr-4 py-3 text-gray-900 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition-all bg-gray-50 focus:bg-white"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1.5">Mot de passe</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
              </div>
              <input
                v-model="password"
                type="password"
                name="password"
                :autocomplete="props.passwordAutocomplete"
                required
                minlength="8"
                placeholder="••••••••"
                class="w-full rounded-xl border border-gray-200 pl-11 pr-4 py-3 text-gray-900 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition-all bg-gray-50 focus:bg-white"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="pending"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all disabled:opacity-60 disabled:hover:translate-y-0 mt-2"
          >
            <i v-if="pending" class="fa-solid fa-circle-notch fa-spin"></i>
            <span v-else>{{ submitLabel }}</span>
          </button>

          <div class="pt-6 mt-6 border-t border-gray-100 text-center text-sm font-medium text-gray-600">
            <slot />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
