export type AdminReview = {
  id: string
  productId: string
  productName: string
  productSlug: string
  author: string
  rating: number
  body: string
  createdAt: string
  reported: boolean
  hidden: boolean
}

export type AdminReviewPage = {
  items: AdminReview[]
  page: number
  limit: number
  total: number
}

export type ListAdminReviewsQuery = {
  page: number
  limit: number
  reportedOnly?: boolean
}
