import type { HttpClient } from '@/shared/http/HttpClient'
import type { NewsletterRepository } from '../application/NewsletterRepository'

export class NewsletterHttpRepository implements NewsletterRepository {
  constructor(private readonly http: HttpClient) {}

  async subscribe(email: string): Promise<void> {
    await this.http.post('/newsletter', { email })
  }
}
