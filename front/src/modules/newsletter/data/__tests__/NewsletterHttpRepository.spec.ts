import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { NewsletterHttpRepository } from '../NewsletterHttpRepository'

describe('NewsletterHttpRepository', () => {
  it('subscribes with POST /newsletter', async () => {
    const http = new FakeHttpClient(() => ({ status: 'subscribed' }))
    const repository = new NewsletterHttpRepository(http)

    await expect(repository.subscribe('ada@shoppy.test')).resolves.toBeUndefined()
    expect(http.calls).toEqual([
      { method: 'POST', path: '/newsletter', body: { email: 'ada@shoppy.test' } },
    ])
  })
})
