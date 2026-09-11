import type { AuthCredentials, AuthRepository } from './AuthRepository'
import type { SessionGateway } from './SessionGateway'

export async function login(
  repository: AuthRepository,
  session: SessionGateway,
  credentials: AuthCredentials,
) {
  const next = await repository.login(credentials)
  session.set(next)
  return next
}
