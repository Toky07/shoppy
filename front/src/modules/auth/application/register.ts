import type { AuthCredentials, AuthRepository } from './AuthRepository'

export async function register(repository: AuthRepository, credentials: AuthCredentials) {
  await repository.register(credentials)
}
