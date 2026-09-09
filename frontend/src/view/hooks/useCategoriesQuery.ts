import { useQuery } from '@tanstack/react-query'
import * as categoryService from '@/service/categoryService'

export const categoriesQueryKey = ['categories'] as const

export function useCategoriesQuery() {
  return useQuery({
    queryKey: categoriesQueryKey,
    queryFn: () => categoryService.listCategories(),
  })
}
