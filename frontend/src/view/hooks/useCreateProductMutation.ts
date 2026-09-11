import { useMutation, useQueryClient } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'
import { productSummaryQueryKey } from '@/view/hooks/useProductSummaryQuery'

export function useCreateProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (values: ProductFormValues) => productService.createProduct(values),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: productsQueryKey }),
        queryClient.invalidateQueries({ queryKey: productSummaryQueryKey }),
      ])
    },
  })
}
