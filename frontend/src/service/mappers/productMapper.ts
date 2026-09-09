import type {
  ProductListResponse,
  ProductResponse,
} from '@/api/responses/productResponse'
import type { Product, ProductListPage } from '@/types/Product'

export function mapProduct(response: ProductResponse): Product {
  return {
    id: response.id,
    productName: response.product_name,
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
