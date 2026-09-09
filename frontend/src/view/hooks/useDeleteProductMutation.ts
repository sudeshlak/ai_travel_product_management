import { useMutation, useQueryClient } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'

export function useDeleteProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) => productService.deleteProduct(id),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: productsQueryKey })
    },
  })
}
