export type ProductReview = {
  id: string
  rating: number
  body: string
  author: string
  createdAt: string
  mine: boolean
}

export type ProductReviewList = {
  items: ProductReview[]
  count: number
  averageRating: number | null
}

export type SubmitReviewInput = {
  rating: number
  body: string
}
