import type { ProductFormValues, ProductStatus } from '@/types/ProductFormValues'

const STATUS_VALUES: ProductStatus[] = ['Active', 'Inactive']

export function validateProductForm(
  values: ProductFormValues,
): Record<string, string> {
  const errors: Record<string, string> = {}

  if (!values.productName.trim()) {
    errors.productName = 'Product name is required.'
  }

  if (!values.categoryId) {
    errors.categoryId = 'Category is required.'
  }

  if (values.destinationIds.length === 0) {
    errors.destinationIds = 'Select at least one destination.'
  }

  if (!values.description.trim()) {
    errors.description = 'Description is required.'
  }

  const price = Number(values.price)
  if (!values.price.trim()) {
    errors.price = 'Price is required.'
  } else if (Number.isNaN(price) || price <= 0) {
    errors.price = 'Price must be greater than 0.'
  }

  const inventory = Number(values.inventoryCount)
  if (!values.inventoryCount.trim()) {
    errors.inventoryCount = 'Inventory count is required.'
  } else if (!Number.isInteger(inventory) || inventory < 0) {
    errors.inventoryCount = 'Inventory must be a whole number of 0 or more.'
  }

  if (!values.validFrom) {
    errors.validFrom = 'Valid from date is required.'
  }

  if (!values.validUntil) {
    errors.validUntil = 'Valid until date is required.'
  } else if (values.validFrom && values.validUntil < values.validFrom) {
    errors.validUntil = 'Valid until must be on or after valid from.'
  }

  if (!STATUS_VALUES.includes(values.status)) {
    errors.status = 'Status is invalid.'
  }

  return errors
}
