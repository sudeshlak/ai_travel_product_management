import { listDestinations as listDestinationsEndpoint } from '@/api/endpoints/destinationEndpoints'
import { mapNamedResourceList } from '@/service/mappers/namedResourceMapper'
import type { ProductDestination } from '@/types/Product'

export async function listDestinations(): Promise<ProductDestination[]> {
  const response = await listDestinationsEndpoint()
  return mapNamedResourceList(response.data) as ProductDestination[]
}
