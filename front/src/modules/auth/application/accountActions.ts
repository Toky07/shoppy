import type { AuthRepository } from './AuthRepository'
import type { AuthSession } from './createAuthSession'

export async function changePassword(
  repository: AuthRepository,
  session: AuthSession,
  currentPassword: string,
  newPassword: string,
) {
  await repository.changePassword(currentPassword, newPassword)
  session.clear()
}

export async function logoutAll(repository: AuthRepository, session: AuthSession) {
  await repository.logoutAll()
  session.clear()
}

export async function deleteAccount(repository: AuthRepository, session: AuthSession, password: string) {
  await repository.deleteAccount(password)
  session.clear()
}

export async function confirmEmailChange(repository: AuthRepository, session: AuthSession, token: string) {
  await repository.confirmEmailChange(token)
  session.clear()
}

export function markEmailVerified(session: AuthSession) {
  const current = session.session.value
  if (!current) {
    return
  }

  session.set({
    ...current,
    user: { ...current.user, emailVerified: true },
  })
}
