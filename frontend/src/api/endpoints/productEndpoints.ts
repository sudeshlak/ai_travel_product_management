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

function unwrapProduct(
  data: { data: ProductResponse } | ProductResponse,
): ProductResponse {
  if (data && typeof data === 'object' && 'data' in data && data.data) {
    return data.data
  }
  return data as ProductResponse
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

export async function getProduct(id: number): Promise<ProductResponse> {
  try {
    const { data } = await client.get<{ data: ProductResponse } | ProductResponse>(
      `/v1/products/${id}`,
    )
    return unwrapProduct(data)
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
    return unwrapProduct(data)
  } catch (error) {
    mapAxiosError(error)
  }
}

export async function updateProduct(
  id: number,
  payload: CreateProductPayload,
): Promise<ProductResponse> {
  try {
    const { data } = await client.put<{ data: ProductResponse } | ProductResponse>(
      `/v1/products/${id}`,
      payload,
    )
    return unwrapProduct(data)
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
