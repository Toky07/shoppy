import { render, screen } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import PageStatus from '../PageStatus.vue'

describe('PageStatus', () => {
  it('shows a loading message', () => {
    render(PageStatus, { props: { status: 'loading' } })

    expect(screen.getByText('Chargement en cours...')).toBeTruthy()
  })

  it('shows an error alert', () => {
    render(PageStatus, { props: { status: 'error', errorMessage: 'Boom.' } })

    expect(screen.getByRole('alert').textContent).toContain('Boom.')
  })

  it('shows the empty slot', () => {
    render(PageStatus, {
      props: { status: 'empty' },
      slots: { empty: 'Rien ici.' },
    })

    expect(screen.getByText('Rien ici.')).toBeTruthy()
  })
})
