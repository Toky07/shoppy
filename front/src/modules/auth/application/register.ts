import type { AuthCredentials, AuthRepository } from './AuthRepository'
import type { SessionGateway } from './SessionGateway'
import { login } from './login'

export async function register(
  repository: AuthRepository,
  session: SessionGateway,
  credentials: AuthCredentials,
) {
  await repository.register(credentials)
  return login(repository, session, credentials)
}
