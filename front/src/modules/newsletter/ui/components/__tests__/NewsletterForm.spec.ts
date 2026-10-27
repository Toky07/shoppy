import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { FakeNewsletterRepository } from '../../../testing/FakeNewsletterRepository'
import { ApiError } from '@/shared/http/ApiError'

describe('NewsletterForm', () => {
  it('subscribes an email from the footer', async () => {
    const newsletterRepository = new FakeNewsletterRepository()
    await renderApp({ newsletterRepository })

    await userEvent.type(screen.getByLabelText("Lettre d'information"), 'ada@shoppy.test')
    await userEvent.click(screen.getByRole('button', { name: "S'abonner" }))

    await waitFor(() => {
      expect(screen.getByRole('status').textContent).toMatch(/inscrit/i)
    })
    expect(newsletterRepository.emails).toEqual(['ada@shoppy.test'])
  })

  it('shows an error when subscribe fails', async () => {
    const newsletterRepository = new FakeNewsletterRepository()
    newsletterRepository.failWith = new ApiError(400, 'validation_error', 'The request is invalid.', [
      { field: 'email', message: 'Email must be a valid email address.' },
    ])
    await renderApp({ newsletterRepository })

    await userEvent.type(screen.getByLabelText("Lettre d'information"), 'ada@shoppy.test')
    await userEvent.click(screen.getByRole('button', { name: "S'abonner" }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toMatch(/adresse email valide/i)
    })
    expect(newsletterRepository.emails).toEqual([])
  })
})
