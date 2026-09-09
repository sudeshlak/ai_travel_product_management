import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type { ProductListResponse } from '@/api/responses/productResponse'

export type ListProductsParams = {
  page: number
  perPage?: number
}

export async function listProducts(
  params: ListProductsParams,
): Promise<ProductListResponse> {
  try {
    const { data } = await client.get<ProductListResponse>('/v1/products', {
      params: {
        page: params.page,
        per_page: params.perPage ?? 15,
      },
    })
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}

export async function deleteProduct(id: number): Promise<void> {
  try {
    await client.delete(`/v1/products/${id}`)
  } catch (error) {
    mapAxiosError(error)
  }
}
