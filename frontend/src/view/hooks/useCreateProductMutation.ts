import { useMutation, useQueryClient } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { productsQueryKey } from '@/view/hooks/useProductsQuery'

export function useCreateProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (values: ProductFormValues) => productService.createProduct(values),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: productsQueryKey })
    },
  })
}
