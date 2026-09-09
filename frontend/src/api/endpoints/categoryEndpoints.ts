import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type { NamedResourceListResponse } from '@/api/responses/namedResourceResponse'

export async function listCategories(): Promise<NamedResourceListResponse> {
  try {
    const { data } = await client.get<NamedResourceListResponse>('/v1/categories')
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}
