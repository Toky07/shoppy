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
      credentials: 'include',
      headers: { Accept: 'application/json' },
    })
  })

  it('posts JSON with the session cookie', async () => {
    const fetchFn = vi.fn().mockResolvedValue(jsonResponse(200, { ok: true }))
    const client = new FetchHttpClient('/api', fetchFn)

    await expect(client.post('/auth/login', { email: 'a@b.c', password: 'secret-secret' })).resolves.toEqual({
      ok: true,
    })
    expect(fetchFn).toHaveBeenCalledWith('/api/auth/login', {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
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

  it('clears a stale session on an unauthenticated response', async () => {
    const clear = vi.fn()
    const fetchFn = vi.fn().mockResolvedValue(
      jsonResponse(401, {
        error: { code: 'unauthenticated', message: 'The request is not authenticated.' },
      }),
    )
    const client = new FetchHttpClient('/api', fetchFn, { current: () => 'stale-token', clear })

    await expect(client.post('/cart/items', { productId: 'p1', quantity: 1 })).rejects.toMatchObject({
      code: 'unauthenticated',
    })
    expect(clear).toHaveBeenCalledOnce()
  })

  it('does not clear the session on invalid credentials', async () => {
    const clear = vi.fn()
    const fetchFn = vi.fn().mockResolvedValue(
      jsonResponse(401, {
        error: { code: 'invalid_credentials', message: 'The provided credentials are invalid.' },
      }),
    )
    const client = new FetchHttpClient('/api', fetchFn, { current: () => 'tok-en', clear })

    await expect(client.post('/auth/login', { email: 'a@b.c', password: 'wrong' })).rejects.toMatchObject({
      code: 'invalid_credentials',
    })
    expect(clear).not.toHaveBeenCalled()
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
