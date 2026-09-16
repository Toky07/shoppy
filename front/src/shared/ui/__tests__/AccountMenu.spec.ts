import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { adminSession, visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeAuthRepository } from '@/modules/auth/testing/FakeAuthRepository'

async function openMenu() {
  const trigger = screen.getByRole('button', { name: /Mon compte/ })
  await userEvent.click(trigger)

  return trigger
}

describe('AccountMenu', () => {
  it('stays closed until the trigger is clicked', async () => {
    await renderApp({ session: visitorSession })

    expect(screen.queryByRole('menu')).toBeNull()
    const trigger = await openMenu()

    expect(screen.getByRole('menu')).toBeTruthy()
    expect(trigger.getAttribute('aria-expanded')).toBe('true')
  })

  it('links to the account pages', async () => {
    await renderApp({ session: visitorSession })
    await openMenu()

    expect(screen.getByRole('menuitem', { name: 'Mon profil' }).getAttribute('href')).toBe(
      '/account',
    )
    expect(screen.getByRole('menuitem', { name: 'Mes favoris' }).getAttribute('href')).toBe(
      '/favorites',
    )
    expect(screen.getByRole('menuitem', { name: 'Mes commandes' }).getAttribute('href')).toBe(
      '/orders',
    )
    expect(screen.getByRole('menuitem', { name: 'Mon panier' }).getAttribute('href')).toBe('/cart')
  })

  it('hides the admin entry from a customer', async () => {
    await renderApp({ session: visitorSession })
    await openMenu()

    expect(screen.queryByRole('menuitem', { name: 'Administration' })).toBeNull()
  })

  it('offers the admin entry to an admin', async () => {
    await renderApp({ session: adminSession })
    await openMenu()

    expect(screen.getByRole('menuitem', { name: 'Administration' }).getAttribute('href')).toBe(
      '/admin',
    )
  })

  it('closes on Escape and gives the focus back to the trigger', async () => {
    await renderApp({ session: visitorSession })
    const trigger = await openMenu()

    await userEvent.keyboard('{Escape}')

    await waitFor(() => {
      expect(screen.queryByRole('menu')).toBeNull()
    })
    expect(document.activeElement).toBe(trigger)
  })

  it('closes once an entry is followed', async () => {
    const { router } = await renderApp({ session: visitorSession })
    await openMenu()

    await userEvent.click(screen.getByRole('menuitem', { name: 'Mes favoris' }))

    await waitFor(() => {
      expect(router.currentRoute.value.path).toBe('/favorites')
    })
    expect(screen.queryByRole('menu')).toBeNull()
  })

  it('logs out from the menu', async () => {
    const authRepository = new FakeAuthRepository()
    await renderApp({ authRepository, session: visitorSession })
    await openMenu()

    await userEvent.click(screen.getByRole('menuitem', { name: 'Déconnexion' }))

    await waitFor(() => {
      expect(screen.getByRole('link', { name: 'Connexion' })).toBeTruthy()
    })
    expect(authRepository.logoutCount).toBe(1)
  })
})
