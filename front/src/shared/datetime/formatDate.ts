export function formatDate(iso: string, locale = 'fr-FR'): string {
  return new Intl.DateTimeFormat(locale, {
    dateStyle: 'long',
    timeZone: 'UTC',
  }).format(new Date(iso))
}
