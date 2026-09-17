export interface AccessTokenProvider {
  current(): string | null
  clear?(): void
}
