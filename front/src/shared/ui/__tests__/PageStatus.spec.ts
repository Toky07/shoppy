import { render, screen } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { localeProvide } from '@/shared/testing/localeProvide'
import PageStatus from '../PageStatus.vue'

describe('PageStatus', () => {
  it('shows a loading message', () => {
    render(PageStatus, { props: { status: 'loading' }, global: { provide: localeProvide() } })

    expect(screen.getByText('Chargement en cours...')).toBeTruthy()
  })

  it('shows an error alert', () => {
    render(PageStatus, {
      props: { status: 'error', errorMessage: 'Boom.' },
      global: { provide: localeProvide() },
    })

    expect(screen.getByRole('alert').textContent).toContain('Boom.')
  })

  it('shows the empty slot', () => {
    render(PageStatus, {
      props: { status: 'empty' },
      slots: { empty: 'Rien ici.' },
      global: { provide: localeProvide() },
    })

    expect(screen.getByText('Rien ici.')).toBeTruthy()
  })
})
