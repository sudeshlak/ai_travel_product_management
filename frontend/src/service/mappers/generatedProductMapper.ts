import type { GenerateProductResponse } from '@/api/responses/generateProductResponse'
import type { GeneratedProduct } from '@/types/GeneratedProduct'

export function mapGeneratedProduct(response: GenerateProductResponse): GeneratedProduct {
  return {
    productName: response.data.product_name,
    description: response.data.description,
    categoryId: response.data.category_id,
  }
}
