import {
  deleteProduct as deleteProductEndpoint,
  listProducts as listProductsEndpoint,
} from '@/api/endpoints/productEndpoints'
import { mapProductList } from '@/service/mappers/productMapper'
import type { ProductListPage } from '@/types/Product'

export type ListProductsInput = {
  page: number
  perPage?: number
}

export async function listProducts(input: ListProductsInput): Promise<ProductListPage> {
  const response = await listProductsEndpoint({
    page: input.page,
    perPage: input.perPage,
  })
  return mapProductList(response)
}

export async function deleteProduct(id: number): Promise<void> {
  await deleteProductEndpoint(id)
}
