import { describe, expect, it } from 'vitest'
import { parseApiError } from '../parseApiError'

describe('parseApiError', () => {
  it('maps the API error envelope', () => {
    const error = parseApiError(404, {
      error: {
        code: 'product_not_found',
        message: 'Product not found.',
      },
    })

    expect(error.status).toBe(404)
    expect(error.code).toBe('product_not_found')
    expect(error.message).toBe('Product not found.')
    expect(error.violations).toEqual([])
  })

  it('maps validation violations', () => {
    const error = parseApiError(400, {
      error: {
        code: 'validation_error',
        message: 'Invalid request.',
        violations: [{ field: 'page', message: 'Must be at least 1.' }],
      },
    })

    expect(error.code).toBe('validation_error')
    expect(error.violations).toEqual([{ field: 'page', message: 'Must be at least 1.' }])
  })

  it('falls back when the body is not an API error', () => {
    const error = parseApiError(500, 'oops')

    expect(error.code).toBe('http_error')
    expect(error.message).toBe('HTTP 500')
  })
})
