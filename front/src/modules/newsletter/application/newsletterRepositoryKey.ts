import type { InjectionKey } from 'vue'
import type { NewsletterRepository } from './NewsletterRepository'

export const newsletterRepositoryKey: InjectionKey<NewsletterRepository> = Symbol('newsletterRepository')
