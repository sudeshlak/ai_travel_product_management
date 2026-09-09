import { listCategories as listCategoriesEndpoint } from '@/api/endpoints/categoryEndpoints'
import { mapNamedResourceList } from '@/service/mappers/namedResourceMapper'
import type { ProductCategory } from '@/types/Product'

export async function listCategories(): Promise<ProductCategory[]> {
  const response = await listCategoriesEndpoint()
  return mapNamedResourceList(response.data) as ProductCategory[]
}
