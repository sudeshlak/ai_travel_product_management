import type { NamedResourceResponse } from '@/api/responses/namedResourceResponse'
import type { ProductCategory, ProductDestination } from '@/types/Product'

export function mapNamedResource(
  response: NamedResourceResponse,
): ProductCategory | ProductDestination {
  return {
    id: response.id,
    name: response.name,
  }
}

export function mapNamedResourceList(
  items: NamedResourceResponse[],
): Array<ProductCategory | ProductDestination> {
  return items.map(mapNamedResource)
}
