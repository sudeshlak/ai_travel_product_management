import { useQuery } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'

export function productQueryKey(id: number) {
  return [...productsQueryKey, id] as const
}

export function useProductQuery(id: number | null) {
  return useQuery({
    queryKey: id === null ? [...productsQueryKey, 'invalid'] : productQueryKey(id),
    queryFn: () => {
      if (id === null) {
        throw new Error('Invalid product id')
      }
      return productService.getProduct(id)
    },
    enabled: id !== null,
  })
}
