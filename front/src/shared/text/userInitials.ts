export function userInitials(email: string) {
  return email.slice(0, 2).toUpperCase()
}
