import { useMutation, useQueryClient } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'
import { productSummaryQueryKey } from '@/view/hooks/useProductSummaryQuery'

export function useDeleteProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) => productService.deleteProduct(id),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: productsQueryKey }),
        queryClient.invalidateQueries({ queryKey: productSummaryQueryKey }),
      ])
    },
  })
}
