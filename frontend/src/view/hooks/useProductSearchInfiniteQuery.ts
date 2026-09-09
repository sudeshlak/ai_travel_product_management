import { useInfiniteQuery } from '@tanstack/react-query'
import * as productService from '@/service/productService'

export const productSearchQueryKey = ['products', 'search'] as const

export const PRODUCT_SEARCH_PAGE_SIZE = 8

export function useProductSearchInfiniteQuery(query: string) {
  return useInfiniteQuery({
    queryKey: [...productSearchQueryKey, query],
    initialPageParam: 1,
    queryFn: ({ pageParam }) =>
      productService.searchProducts({
        query,
        page: pageParam,
        perPage: PRODUCT_SEARCH_PAGE_SIZE,
      }),
    getNextPageParam: (lastPage) =>
      lastPage.page < lastPage.pageCount ? lastPage.page + 1 : undefined,
  })
}
