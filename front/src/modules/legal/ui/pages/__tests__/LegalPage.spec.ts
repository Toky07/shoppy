import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'

describe('LegalPage', () => {
  it('opens the terms of sale from the footer', async () => {
    await renderApp()

    await userEvent.click(screen.getByRole('link', { name: 'CGV' }))

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Conditions générales de vente' })).toBeTruthy()
    })
    expect(screen.getByRole('heading', { name: 'Droit de rétractation' })).toBeTruthy()
    expect(document.title).toBe('Conditions générales de vente — Shoppy')
  })

  it('shows the privacy policy', async () => {
    await renderApp({ path: '/confidentialite' })

    expect(screen.getByRole('heading', { name: 'Politique de confidentialité' })).toBeTruthy()
    expect(screen.getByRole('heading', { name: 'Vos droits' })).toBeTruthy()
    expect(screen.getByRole('heading', { name: 'Cookies' })).toBeTruthy()
  })

  it('shows the legal notices', async () => {
    await renderApp({ path: '/mentions' })

    expect(screen.getByRole('heading', { name: 'Mentions légales', level: 1 })).toBeTruthy()
    expect(screen.getByRole('heading', { name: 'Éditeur' })).toBeTruthy()
  })

  it('shows delivery and returns', async () => {
    await renderApp({ path: '/retours' })

    expect(screen.getByRole('heading', { name: 'Livraison et retours' })).toBeTruthy()
    expect(screen.getByRole('heading', { name: 'Remboursement' })).toBeTruthy()
    expect(screen.getByText(/étiquette/)).toBeTruthy()
  })
})
