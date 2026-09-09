import { useQuery } from '@tanstack/react-query'
import * as productService from '@/service/productService'

export const productsQueryKey = ['products'] as const

export function useProductsQuery(page: number, perPage = 15) {
  return useQuery({
    queryKey: [...productsQueryKey, page, perPage],
    queryFn: () => productService.listProducts({ page, perPage }),
  })
}
