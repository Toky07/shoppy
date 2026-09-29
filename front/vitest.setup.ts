import { cleanup } from '@testing-library/vue'
import { afterEach } from 'vitest'

class MockIntersectionObserver implements IntersectionObserver {
  readonly root: Element | Document | null = null
  readonly rootMargin = '0px'
  readonly scrollMargin = '0px'
  readonly thresholds: readonly number[] = [0]

  constructor(private callback: IntersectionObserverCallback) {}

  observe(target: Element): void {
    this.callback([{ isIntersecting: true, target } as IntersectionObserverEntry], this)
  }

  unobserve(): void {}

  disconnect(): void {}

  takeRecords(): IntersectionObserverEntry[] {
    return []
  }
}

window.IntersectionObserver = MockIntersectionObserver as unknown as typeof IntersectionObserver

// happy-dom has no dialogs; mirror jsdom, where confirm() answers "cancel" unless a test mocks it.
window.confirm ??= () => false

// happy-dom checks steps with a raw float modulo, so 29.99 with step="0.01" is wrongly invalid.
Object.defineProperty(ValidityState.prototype, 'stepMismatch', {
  configurable: true,
  get(this: ValidityState & { element: HTMLInputElement }) {
    const input = this.element
    const step = input.getAttribute('step')
    if (input.localName !== 'input' || !['number', 'range'].includes(input.type) || input.value === '' || step === 'any') {
      return false
    }

    const stepSize = step === null ? 1 : Number(step)
    const base = Number(input.getAttribute('min') ?? 0)
    const steps = (Number(input.value) - base) / stepSize

    return Math.abs(steps - Math.round(steps)) > 1e-9
  },
})

afterEach(() => {
  cleanup()
})
