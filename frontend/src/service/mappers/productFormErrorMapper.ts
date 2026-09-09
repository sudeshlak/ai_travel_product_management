import type { ProductFormValues } from '@/types/ProductFormValues'

const serverFieldToFormField: Record<string, keyof ProductFormValues> = {
  product_name: 'productName',
  productName: 'productName',
  category_id: 'categoryId',
  categoryId: 'categoryId',
  description: 'description',
  price: 'price',
  inventory_count: 'inventoryCount',
  inventoryCount: 'inventoryCount',
  valid_from: 'validFrom',
  validFrom: 'validFrom',
  valid_until: 'validUntil',
  validUntil: 'validUntil',
  status: 'status',
  destination_ids: 'destinationIds',
  destinationIds: 'destinationIds',
}

export function mapServerErrors(fields: Record<string, string>): Record<string, string> {
  const mapped: Record<string, string> = {}
  for (const [key, message] of Object.entries(fields)) {
    const formKey = serverFieldToFormField[key] ?? key
    mapped[formKey] = message
  }
  return mapped
}
