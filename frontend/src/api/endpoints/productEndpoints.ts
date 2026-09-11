import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type { GenerateDescriptionResponse } from '@/api/responses/generateDescriptionResponse'
import type {
  ProductListResponse,
  ProductResponse,
} from '@/api/responses/productResponse'
import type { ProductSummaryResponse } from '@/api/responses/productSummaryResponse'
import type { CreateProductPayload } from '@/types/ProductFormValues'

export type GenerateProductDescriptionPayload = {
  description: string
  product_name?: string | null
  category?: string | null
}

export type ListProductsParams = {
  page: number
  perPage?: number
}

export type SearchProductsParams = {
  query: string
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

export async function searchProducts(
  params: SearchProductsParams,
): Promise<ProductListResponse> {
  try {
    const { data } = await client.post<ProductListResponse>('/v1/products/search', {
      query: params.query,
      page: params.page,
      per_page: params.perPage ?? 8,
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

export async function generateProductDescription(
  payload: GenerateProductDescriptionPayload,
): Promise<GenerateDescriptionResponse> {
  try {
    const { data } = await client.post<GenerateDescriptionResponse>(
      '/v1/products/generate-description',
      payload,
    )
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}

export async function getProductSummary(): Promise<ProductSummaryResponse> {
  try {
    const { data } = await client.get<ProductSummaryResponse>('/v1/products/summary')
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}
