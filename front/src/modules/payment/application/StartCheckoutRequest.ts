export type StartCheckoutRequest = {
  orderId: string
  provider: string
  successUrl: string
  cancelUrl: string
}
