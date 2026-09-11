import type { ProductSummaryResponse } from '@/api/responses/productSummaryResponse'
import type { ProductSummary } from '@/types/ProductSummary'

export function mapProductSummary(response: ProductSummaryResponse): ProductSummary {
  return {
    totalProducts: response.data.total_products,
    activeProducts: response.data.active_products,
    expiredProducts: response.data.expired_products,
  }
}
