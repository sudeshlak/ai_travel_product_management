import { useMutation } from '@tanstack/react-query'
import * as productService from '@/service/productService'

export function useGenerateProductMutation() {
  return useMutation({
    mutationFn: (prompt: string) => productService.generateProduct(prompt),
  })
}
