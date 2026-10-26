import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const frontRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../../..')

function read(relative: string): string {
  return readFileSync(resolve(frontRoot, relative), 'utf8')
}

describe('unused Pinia', () => {
  it('is not registered and is not a project dependency', () => {
    const entry = read('src/main.ts')
    const pkg = JSON.parse(read('package.json')) as {
      dependencies?: Record<string, string>
      devDependencies?: Record<string, string>
    }

    expect(entry).not.toContain('pinia')
    expect(entry).not.toContain('createPinia')
    expect(pkg.dependencies ?? {}).not.toHaveProperty('pinia')
    expect(pkg.devDependencies ?? {}).not.toHaveProperty('pinia')
  })
})
