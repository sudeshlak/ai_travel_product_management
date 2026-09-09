import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type { NamedResourceListResponse } from '@/api/responses/namedResourceResponse'

export async function listDestinations(): Promise<NamedResourceListResponse> {
  try {
    const { data } = await client.get<NamedResourceListResponse>('/v1/destinations')
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}
