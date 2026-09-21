import { fileURLToPath, URL } from 'node:url'

import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'

function envEnabled(name: string, fallback = false): boolean {
  const value = process.env[name]
  if (value === undefined) {
    return fallback
  }

  return value === '1' || value === 'true' || value === 'TRUE'
}

const inDocker = envEnabled('VITE_IN_DOCKER')
const usePolling = envEnabled('VITE_USE_POLLING', inDocker)
const vitePort = Number(process.env.VITE_PORT ?? 5173)
const hmrPort = Number(process.env.VITE_HMR_CLIENT_PORT ?? vitePort)
const apiProxyTarget = process.env.API_PROXY_TARGET ?? 'http://127.0.0.1:8000'

const proxy = {
  '/api': {
    target: apiProxyTarget,
    changeOrigin: true,
    rewrite: (path: string) => path.replace(/^\/api/, ''),
  },
  '/media': {
    target: apiProxyTarget,
    changeOrigin: true,
  },
  '/uploads': {
    target: apiProxyTarget,
    changeOrigin: true,
  },
}

export default defineConfig({
  plugins: [vue(), tailwindcss(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    port: vitePort,
    strictPort: inDocker,
    allowedHosts: true,
    clearScreen: !inDocker,
    watch: {
      usePolling,
      ...(usePolling ? { interval: 400 } : {}),
    },
    hmr: inDocker
      ? {
          host: process.env.VITE_HMR_HOST ?? 'localhost',
          port: hmrPort,
          clientPort: hmrPort,
        }
      : true,
    proxy,
  },
  preview: {
    host: true,
    port: vitePort,
    proxy,
  },
})
