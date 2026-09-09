export type ProductCategory = {
  id: number
  name: string
}

export type ProductDestination = {
  id: number
  name: string
}

export type Product = {
  id: number
  productName: string
  description: string
  price: string
  inventoryCount: number
  validFrom: string
  validUntil: string
  status: string
  category: ProductCategory | null
  destinations: ProductDestination[]
}

export type ProductListPage = {
  items: Product[]
  page: number
  pageCount: number
  total: number
  perPage: number
}
