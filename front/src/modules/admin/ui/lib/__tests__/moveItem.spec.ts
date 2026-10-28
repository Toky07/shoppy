import { describe, expect, it } from 'vitest'
import { moveItem } from '../moveItem'

describe('moveItem', () => {
  it('moves an item to a new index', () => {
    expect(moveItem(['a', 'b', 'c'], 0, 2)).toEqual(['b', 'c', 'a'])
    expect(moveItem(['a', 'b', 'c'], 2, 0)).toEqual(['c', 'a', 'b'])
  })

  it('returns the same list when the index does not change', () => {
    const items = ['a', 'b']
    expect(moveItem(items, 1, 1)).toBe(items)
    expect(moveItem(items, -1, 0)).toBe(items)
    expect(moveItem(items, 0, 8)).toBe(items)
  })
})
