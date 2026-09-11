export function parsePageQuery(value: unknown): number {
  const raw = Array.isArray(value) ? value[0] : value
  const parsed = Number(raw)

  return Number.isInteger(parsed) && parsed >= 1 ? parsed : 1
}
