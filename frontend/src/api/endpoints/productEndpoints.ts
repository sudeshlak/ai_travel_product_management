import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type {
  ProductListResponse,
  ProductResponse,
} from '@/api/responses/productResponse'
import type { CreateProductPayload } from '@/types/ProductFormValues'

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

export async function createProduct(
  payload: CreateProductPayload,
): Promise<ProductResponse> {
  try {
    const { data } = await client.post<{ data: ProductResponse } | ProductResponse>(
      '/v1/products',
      payload,
    )
    if (data && typeof data === 'object' && 'data' in data && data.data) {
      return data.data
    }
    return data as ProductResponse
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
