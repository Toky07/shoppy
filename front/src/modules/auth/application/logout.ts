import type { AuthRepository } from './AuthRepository'
import type { SessionGateway } from './SessionGateway'

export async function logout(repository: AuthRepository, session: SessionGateway) {
  try {
    await repository.logout()
  } catch {
    // Keep local logout even if the token is already invalid.
  } finally {
    session.clear()
  }
}
