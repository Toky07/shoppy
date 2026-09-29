export function stockLabel(stock: number): string {
  return stock > 0 ? `En stock (${stock})` : 'Rupture de stock'
}
