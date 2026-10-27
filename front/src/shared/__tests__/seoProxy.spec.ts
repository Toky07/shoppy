import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const frontRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../../..')

function read(relative: string): string {
  return readFileSync(resolve(frontRoot, relative), 'utf8')
}

describe('storefront SEO proxy', () => {
  it('forwards sitemap and robots to the API in development', () => {
    const config = read('vite.config.ts')

    expect(config).toContain("'/sitemap.xml'")
    expect(config).toContain("'/robots.txt'")
  })

  it('serves sitemap and robots from nginx instead of the SPA shell', () => {
    const nginx = read('docker/nginx.conf')

    expect(nginx).toContain('location = /sitemap.xml')
    expect(nginx).toContain('location = /robots.txt')
    expect(nginx).toContain('proxy_pass $api_upstream')
  })
})
