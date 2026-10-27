export type NewsletterRepository = {
  subscribe(email: string): Promise<void>
}
