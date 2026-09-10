import { useMutation } from '@tanstack/react-query'
import * as productService from '@/service/productService'
import type { GenerateProductDescriptionInput } from '@/service/productService'

export function useGenerateDescriptionMutation() {
  return useMutation({
    mutationFn: (input: GenerateProductDescriptionInput) =>
      productService.generateProductDescription(input),
  })
}
