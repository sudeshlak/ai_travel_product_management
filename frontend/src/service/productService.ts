import {
  createProduct as createProductEndpoint,
  deleteProduct as deleteProductEndpoint,
  generateProduct as generateProductEndpoint,
  getProduct as getProductEndpoint,
  getProductSummary as getProductSummaryEndpoint,
  listProducts as listProductsEndpoint,
  searchProducts as searchProductsEndpoint,
  updateProduct as updateProductEndpoint,
} from '@/api/endpoints/productEndpoints'
import { mapGeneratedProduct } from '@/service/mappers/generatedProductMapper'
import { mapProduct, mapProductList } from '@/service/mappers/productMapper'
import { mapProductSummary } from '@/service/mappers/productSummaryMapper'
import type { GeneratedProduct } from '@/types/GeneratedProduct'
import type { Product, ProductListPage } from '@/types/Product'
import type {
  CreateProductPayload,
  ProductFormValues,
} from '@/types/ProductFormValues'
import type { ProductSummary } from '@/types/ProductSummary'

export type ListProductsInput = {
  page: number
  perPage?: number
}

export type SearchProductsInput = {
  query: string
  page: number
  perPage?: number
}

export async function listProducts(input: ListProductsInput): Promise<ProductListPage> {
  const response = await listProductsEndpoint({
    page: input.page,
    perPage: input.perPage,
  })
  return mapProductList(response)
}

export async function searchProducts(
  input: SearchProductsInput,
): Promise<ProductListPage> {
  const response = await searchProductsEndpoint({
    query: input.query,
    page: input.page,
    perPage: input.perPage,
  })
  return mapProductList(response)
}

export async function getProduct(id: number): Promise<Product> {
  const response = await getProductEndpoint(id)
  return mapProduct(response)
}

export function toCreateProductPayload(values: ProductFormValues): CreateProductPayload {
  return {
    product_name: values.productName.trim(),
    category_id: Number(values.categoryId),
    description: values.description.trim(),
    price: Number(values.price),
    inventory_count: Number(values.inventoryCount),
    valid_from: values.validFrom,
    valid_until: values.validUntil,
    status: values.status,
    destination_ids: values.destinationIds,
  }
}

export async function createProduct(values: ProductFormValues): Promise<Product> {
  const response = await createProductEndpoint(toCreateProductPayload(values))
  return mapProduct(response)
}

export async function updateProduct(
  id: number,
  values: ProductFormValues,
): Promise<Product> {
  const response = await updateProductEndpoint(id, toCreateProductPayload(values))
  return mapProduct(response)
}

export async function deleteProduct(id: number): Promise<void> {
  await deleteProductEndpoint(id)
}

export async function generateProduct(prompt: string): Promise<GeneratedProduct> {
  const response = await generateProductEndpoint({ prompt: prompt.trim() })
  return mapGeneratedProduct(response)
}

export async function getProductSummary(): Promise<ProductSummary> {
  const response = await getProductSummaryEndpoint()
  return mapProductSummary(response)
}
