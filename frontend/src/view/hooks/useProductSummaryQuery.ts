import { useQuery } from '@tanstack/react-query'
import * as productService from '@/service/productService'

export const productSummaryQueryKey = ['products', 'summary'] as const

export function useProductSummaryQuery() {
  return useQuery({
    queryKey: productSummaryQueryKey,
    queryFn: () => productService.getProductSummary(),
  })
}
