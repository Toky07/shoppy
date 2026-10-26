import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const frontRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../../..')

describe('vite vue-devtools', () => {
  it('is enabled only for the development server', () => {
    const config = readFileSync(resolve(frontRoot, 'vite.config.ts'), 'utf8')

    expect(config).toContain('vueDevTools')
    expect(config).toMatch(/command === 'serve'/)
    expect(config).not.toMatch(/plugins:\s*\[vue\(\),\s*tailwindcss\(\),\s*vueDevTools\(\)\]/)
  })
})
