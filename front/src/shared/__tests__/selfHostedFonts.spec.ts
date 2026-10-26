import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const frontRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../../..')

function read(relative: string): string {
  return readFileSync(resolve(frontRoot, relative), 'utf8')
}

describe('self-hosted fonts', () => {
  it('does not load Google Fonts', () => {
    const html = read('index.html')
    const csp = read('docker/security-headers.conf')

    expect(html).not.toContain('fonts.googleapis.com')
    expect(html).not.toContain('fonts.gstatic.com')
    expect(csp).not.toContain('fonts.googleapis.com')
    expect(csp).not.toContain('fonts.gstatic.com')
    expect(csp).toMatch(/font-src 'self'/)
  })

  it('ships Inter and Bricolage Grotesque as local variable fonts', () => {
    const entry = read('src/main.ts')
    const css = read('src/assets/main.css')
    const pkg = JSON.parse(read('package.json')) as {
      dependencies?: Record<string, string>
      devDependencies?: Record<string, string>
    }
    const deps = { ...pkg.dependencies, ...pkg.devDependencies }

    expect(entry).toContain("import '@fontsource-variable/inter'")
    expect(entry).toContain("import '@fontsource-variable/bricolage-grotesque/standard.css'")
    expect(css).toContain("'Inter Variable'")
    expect(css).toContain("'Bricolage Grotesque Variable'")
    expect(deps).toHaveProperty('@fontsource-variable/inter')
    expect(deps).toHaveProperty('@fontsource-variable/bricolage-grotesque')
  })
})
