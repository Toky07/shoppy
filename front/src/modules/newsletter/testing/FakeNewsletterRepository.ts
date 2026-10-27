import type { NewsletterRepository } from '../application/NewsletterRepository'

export class FakeNewsletterRepository implements NewsletterRepository {
  public readonly emails: string[] = []
  public failWith: Error | null = null

  async subscribe(email: string): Promise<void> {
    if (this.failWith) {
      throw this.failWith
    }

    this.emails.push(email)
  }
}
