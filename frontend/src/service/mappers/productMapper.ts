import type {
  ProductListResponse,
  ProductResponse,
} from '@/api/responses/productResponse'
import type { Product, ProductListPage } from '@/types/Product'
import type { ProductFormValues, ProductStatus } from '@/types/ProductFormValues'

export function mapProduct(response: ProductResponse): Product {
  return {
    id: response.id,
    productName: response.product_name,
    description: response.description ?? '',
    price: response.price,
    inventoryCount: response.inventory_count,
    validFrom: response.valid_from,
    validUntil: response.valid_until,
    status: response.status,
    category: response.category
      ? { id: response.category.id, name: response.category.name }
      : null,
    destinations: (response.destinations ?? []).map((destination) => ({
      id: destination.id,
      name: destination.name,
    })),
  }
}

export function mapProductList(response: ProductListResponse): ProductListPage {
  return {
    items: response.data.map(mapProduct),
    page: response.meta.current_page,
    pageCount: response.meta.last_page,
    total: response.meta.total,
    perPage: response.meta.per_page,
  }
}

export function mapProductToFormValues(product: Product): ProductFormValues {
  const status: ProductStatus =
    product.status === 'Inactive' ? 'Inactive' : 'Active'

  return {
    productName: product.productName,
    categoryId: product.category ? String(product.category.id) : '',
    description: product.description,
    price: String(product.price),
    inventoryCount: String(product.inventoryCount),
    validFrom: product.validFrom,
    validUntil: product.validUntil,
    status,
    destinationIds: product.destinations.map((destination) => destination.id),
  }
}
