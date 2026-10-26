import { describe, expect, it } from 'vitest'
import type { RouteLocationNormalized } from 'vue-router'
import { scrollBehavior } from '../scrollBehavior'

function route(path: string, hash = ''): RouteLocationNormalized {
  return {
    path,
    hash,
    fullPath: `${path}${hash}`,
    query: {},
    params: {},
    matched: [],
    name: undefined,
    redirectedFrom: undefined,
    meta: {},
  }
}

describe('scrollBehavior', () => {
  it('scrolls to the top on navigation', () => {
    expect(scrollBehavior(route('/?page=2'), route('/'), null)).toEqual({ top: 0, left: 0 })
  })

  it('scrolls to a hash target', () => {
    expect(scrollBehavior(route('/', '#main'), route('/'), null)).toEqual({ el: '#main' })
  })
})
