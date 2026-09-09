export type ProductStatus = 'Active' | 'Inactive'

export type ProductFormValues = {
  productName: string
  categoryId: string
  description: string
  price: string
  inventoryCount: string
  validFrom: string
  validUntil: string
  status: ProductStatus
  destinationIds: number[]
}

export type CreateProductPayload = {
  product_name: string
  category_id: number
  description: string
  price: number
  inventory_count: number
  valid_from: string
  valid_until: string
  status: ProductStatus
  destination_ids: number[]
}

export const emptyProductFormValues: ProductFormValues = {
  productName: '',
  categoryId: '',
  description: '',
  price: '',
  inventoryCount: '',
  validFrom: '',
  validUntil: '',
  status: 'Active',
  destinationIds: [],
}
