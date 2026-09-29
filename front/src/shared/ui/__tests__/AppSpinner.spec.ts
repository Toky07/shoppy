import { render, screen } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import AppSpinner from '../AppSpinner.vue'

describe('AppSpinner', () => {
  it('exposes a status role with an accessible label', () => {
    render(AppSpinner, { props: { label: 'Chargement de la photo' } })

    expect(screen.getByRole('status', { name: 'Chargement de la photo' })).toBeTruthy()
  })
})
