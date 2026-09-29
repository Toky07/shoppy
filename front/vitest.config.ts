import { fileURLToPath } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vitest/config'

const root = fileURLToPath(new URL('./', import.meta.url))
const src = fileURLToPath(new URL('./src', import.meta.url))

const shared = {
  plugins: [vue()],
  resolve: {
    alias: {
      '@': src,
    },
  },
}

const domInclude = [
  'src/__tests__/**/*.spec.ts',
  'src/shared/ui/**/*.spec.ts',
  'src/modules/**/ui/pages/__tests__/**/*.spec.ts',
  'src/modules/**/ui/components/__tests__/**/*.spec.ts',
  'src/modules/cart/application/__tests__/createCartState.spec.ts',
]

export default defineConfig({
  ...shared,
  test: {
    root,
    css: false,
    pool: 'threads',
    isolate: false,
    experimental: {
      fsModuleCache: true,
    },
    projects: [
      {
        ...shared,
        test: {
          name: 'unit',
          root,
          environment: 'node',
          include: ['src/**/*.spec.ts'],
          exclude: ['e2e/**', 'node_modules/**', ...domInclude],
          isolate: false,
        },
      },
      {
        ...shared,
        test: {
          name: 'component',
          root,
          environment: 'happy-dom',
          setupFiles: [fileURLToPath(new URL('./vitest.setup.ts', import.meta.url))],
          include: domInclude,
          isolate: false,
          css: false,
        },
      },
    ],
  },
})
