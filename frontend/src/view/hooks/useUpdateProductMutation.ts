import { useMutation, useQueryClient } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { productQueryKey } from '@/view/hooks/useProductQuery'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'
import { productSummaryQueryKey } from '@/view/hooks/useProductSummaryQuery'

export type UpdateProductInput = {
  id: number
  values: ProductFormValues
}

export function useUpdateProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, values }: UpdateProductInput) =>
      productService.updateProduct(id, values),
    onSuccess: async (_product, variables) => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: productsQueryKey }),
        queryClient.invalidateQueries({ queryKey: productQueryKey(variables.id) }),
        queryClient.invalidateQueries({ queryKey: productSummaryQueryKey }),
      ])
    },
  })
}
