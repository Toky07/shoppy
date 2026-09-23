import type { AuthRepository } from './AuthRepository'
import type { AuthSession } from './createAuthSession'

export async function restoreAuthSession(repository: AuthRepository, session: AuthSession): Promise<void> {
  try {
    session.set({ user: await repository.currentUser() })
  } catch {
    session.clear()
  }
}
