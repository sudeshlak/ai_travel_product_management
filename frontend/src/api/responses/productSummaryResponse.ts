export type ProductSummaryResponseData = {
  total_products: number
  active_products: number
  expired_products: number
}

export type ProductSummaryResponse = {
  data: ProductSummaryResponseData
}
