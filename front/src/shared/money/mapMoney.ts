import type { Money } from './Money'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'

export function mapMoney(value: unknown, invalidMessage = 'Invalid money payload.'): Money {
  if (!isRecord(value) || typeof value.cents !== 'number' || typeof value.currency !== 'string') {
    throw new InvalidResponseError(invalidMessage)
  }

  return { cents: value.cents, currency: value.currency }
}
