export function catalogCentsFromEuros(value: unknown): number | undefined {
  if (typeof value !== 'string' || !/^\d+$/.test(value)) {
    return undefined
  }

  const euros = Number(value)
  if (euros > 1_000_000) {
    return undefined
  }

  return euros * 100
}

export function isInStockQuery(value: unknown): boolean {
  return value === '1'
}
