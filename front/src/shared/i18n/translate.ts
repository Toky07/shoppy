export type MessageTree = {
  [key: string]: string | MessageTree
}

export type Translate = (key: string, params?: Record<string, string | number>) => string

export function lookupMessage(tree: MessageTree, key: string): string | undefined {
  let current: string | MessageTree = tree

  for (const part of key.split('.')) {
    if (typeof current !== 'object' || current === null || !(part in current)) {
      return undefined
    }

    current = current[part] as string | MessageTree
  }

  return typeof current === 'string' ? current : undefined
}

export function interpolate(template: string, params?: Record<string, string | number>): string {
  if (params === undefined) {
    return template
  }

  return template.replace(/\{(\w+)\}/g, (_, name: string) =>
    params[name] === undefined ? `{${name}}` : String(params[name]),
  )
}

export function translate(
  tree: MessageTree,
  key: string,
  params?: Record<string, string | number>,
): string {
  return interpolate(lookupMessage(tree, key) ?? key, params)
}
