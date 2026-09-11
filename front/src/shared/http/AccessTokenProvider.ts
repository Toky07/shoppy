export interface AccessTokenProvider {
  current(): string | null
}
