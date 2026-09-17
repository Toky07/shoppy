import { describe, expect, it } from 'vitest'
import { userInitials } from '../userInitials'

describe('userInitials', () => {
  it('takes the first two characters of the email', () => {
    expect(userInitials('visitor@shoppy.test')).toBe('VI')
  })

  it('uppercases the initials', () => {
    expect(userInitials('admin@shoppy.test')).toBe('AD')
  })
})
