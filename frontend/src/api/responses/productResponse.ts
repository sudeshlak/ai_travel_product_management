export type ProductCategoryResponse = {
  id: number
  name: string
}

export type ProductDestinationResponse = {
  id: number
  name: string
}

export type ProductResponse = {
  id: number
  product_name: string
  description?: string
  price: string
  inventory_count: number
  valid_from: string
  valid_until: string
  status: string
  category?: ProductCategoryResponse | null
  destinations?: ProductDestinationResponse[]
}

export type ProductListMetaResponse = {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export type ProductListResponse = {
  data: ProductResponse[]
  meta: ProductListMetaResponse
}
