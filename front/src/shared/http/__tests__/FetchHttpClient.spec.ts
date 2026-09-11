import { describe, expect, it, vi } from 'vitest'
import { FetchHttpClient } from '../FetchHttpClient'

function jsonResponse(status: number, body: unknown): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json' },
  })
}

describe('FetchHttpClient', () => {
  it('gets JSON from the joined URL', async () => {
    const fetchFn = vi.fn().mockResolvedValue(jsonResponse(200, { ok: true }))
    const client = new FetchHttpClient('https://api.test', fetchFn)

    await expect(client.get('/products')).resolves.toEqual({ ok: true })
    expect(fetchFn).toHaveBeenCalledWith('https://api.test/products', {
      method: 'GET',
      headers: { Accept: 'application/json' },
    })
  })

  it('posts JSON and sends a bearer token', async () => {
    const fetchFn = vi.fn().mockResolvedValue(jsonResponse(200, { ok: true }))
    const client = new FetchHttpClient('/api', fetchFn, { current: () => 'tok-en' })

    await expect(client.post('/auth/login', { email: 'a@b.c', password: 'secret-secret' })).resolves.toEqual({
      ok: true,
    })
    expect(fetchFn).toHaveBeenCalledWith('/api/auth/login', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: 'Bearer tok-en',
      },
      body: JSON.stringify({ email: 'a@b.c', password: 'secret-secret' }),
    })
  })

  it('returns undefined on 204', async () => {
    const fetchFn = vi.fn().mockResolvedValue(new Response(null, { status: 204 }))
    const client = new FetchHttpClient('/api', fetchFn)

    await expect(client.post('/auth/logout')).resolves.toBeUndefined()
  })

  it('sends PUT and DELETE requests', async () => {
    const fetchFn = vi
      .fn()
      .mockResolvedValueOnce(jsonResponse(200, { ok: true }))
      .mockResolvedValueOnce(jsonResponse(200, { ok: true }))
      .mockResolvedValueOnce(jsonResponse(200, { ok: true }))
    const client = new FetchHttpClient('/api', fetchFn)

    await client.put('/cart/items/p1', { quantity: 2 })
    await client.patch('/products/p1', { name: 'Tee' })
    await client.delete('/cart/items/p1')

    expect(fetchFn).toHaveBeenNthCalledWith(1, '/api/cart/items/p1', expect.objectContaining({ method: 'PUT' }))
    expect(fetchFn).toHaveBeenNthCalledWith(2, '/api/products/p1', expect.objectContaining({ method: 'PATCH' }))
    expect(fetchFn).toHaveBeenNthCalledWith(3, '/api/cart/items/p1', expect.objectContaining({ method: 'DELETE' }))
  })

  it('appends query parameters', async () => {
    const fetchFn = vi.fn().mockResolvedValue(jsonResponse(200, { items: [] }))
    const client = new FetchHttpClient('/api', fetchFn)

    await client.get('/products', { page: 2, limit: 20, unused: undefined })

    expect(fetchFn).toHaveBeenCalledWith('/api/products?page=2&limit=20', expect.anything())
  })

  it('throws a parsed ApiError on failure', async () => {
    const fetchFn = vi.fn().mockResolvedValue(
      jsonResponse(404, {
        error: { code: 'product_not_found', message: 'Product not found.' },
      }),
    )
    const client = new FetchHttpClient('/api', fetchFn)

    await expect(client.get('/products/missing')).rejects.toMatchObject({
      status: 404,
      code: 'product_not_found',
      message: 'Product not found.',
    })
  })

  it('throws a fallback ApiError when the body is not JSON', async () => {
    const fetchFn = vi.fn().mockResolvedValue(new Response('nope', { status: 502 }))
    const client = new FetchHttpClient('/api', fetchFn)

    await expect(client.get('/products')).rejects.toMatchObject({
      status: 502,
      code: 'http_error',
      message: 'HTTP 502',
    })
  })
})
