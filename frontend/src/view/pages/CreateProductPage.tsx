import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import {
  ConnectionError,
  UnauthorizedError,
  UnexpectedError,
  ValidationError,
} from '@/api/errors'
import type { ProductFormValues } from '@/types/ProductFormValues'
import AppHeader from '@/view/components/layout/AppHeader'
import ProductForm from '@/view/components/products/ProductForm'
import { withAuth } from '@/view/hoc/withAuth'
import { useCategoriesQuery } from '@/view/hooks/useCategoriesQuery'
import { useCreateProductMutation } from '@/view/hooks/useCreateProductMutation'
import { useDestinationsQuery } from '@/view/hooks/useDestinationsQuery'
import './CreateProductPage.scss'

const serverFieldToFormField: Record<string, keyof ProductFormValues> = {
  product_name: 'productName',
  productName: 'productName',
  category_id: 'categoryId',
  categoryId: 'categoryId',
  description: 'description',
  price: 'price',
  inventory_count: 'inventoryCount',
  inventoryCount: 'inventoryCount',
  valid_from: 'validFrom',
  validFrom: 'validFrom',
  valid_until: 'validUntil',
  validUntil: 'validUntil',
  status: 'status',
  destination_ids: 'destinationIds',
  destinationIds: 'destinationIds',
}

function mapServerErrors(fields: Record<string, string>): Record<string, string> {
  const mapped: Record<string, string> = {}
  for (const [key, message] of Object.entries(fields)) {
    const formKey = serverFieldToFormField[key] ?? key
    mapped[formKey] = message
  }
  return mapped
}

function CreateProductPage() {
  const navigate = useNavigate()
  const categoriesQuery = useCategoriesQuery()
  const destinationsQuery = useDestinationsQuery()
  const createMutation = useCreateProductMutation()
  const [serverErrors, setServerErrors] = useState<Record<string, string>>({})
  const [formError, setFormError] = useState<string | undefined>()

  const lookupsLoading = categoriesQuery.isLoading || destinationsQuery.isLoading
  const lookupsError = categoriesQuery.error ?? destinationsQuery.error

  const lookupErrorMessage = useMemo(() => {
    if (!lookupsError) {
      return undefined
    }
    if (lookupsError instanceof UnauthorizedError) {
      return 'Your session expired. Please sign in again.'
    }
    if (lookupsError instanceof ConnectionError) {
      return 'Unable to connect. Check your network and try again.'
    }
    if (lookupsError instanceof UnexpectedError) {
      return 'Something went wrong loading form options.'
    }
    return 'Something went wrong loading form options.'
  }, [lookupsError])

  async function handleSubmit(values: ProductFormValues) {
    setFormError(undefined)
    setServerErrors({})
    try {
      await createMutation.mutateAsync(values)
      navigate('/products', { replace: true })
    } catch (error) {
      if (error instanceof ValidationError) {
        setServerErrors(mapServerErrors(error.fields))
        return
      }
      if (error instanceof UnauthorizedError) {
        setFormError('Your session expired. Please sign in again.')
        return
      }
      if (error instanceof ConnectionError) {
        setFormError('Unable to connect. Check your network and try again.')
        return
      }
      setFormError('Could not create the product. Please try again.')
    }
  }

  return (
    <div className="create-product-page">
      <AppHeader primaryAction={{ label: 'Back to products', to: '/products' }} />
      <main className="container pb-4">
        <div className="row justify-content-center">
          <div className="col-12 col-md-8 col-lg-6">
            <h1 className="h3 mb-2">Create product</h1>
            <p className="text-secondary mb-4">Add a new travel product you own.</p>

            {lookupErrorMessage ? (
              <div className="alert alert-danger" role="alert">
                {lookupErrorMessage}
              </div>
            ) : null}

            {formError ? (
              <div className="alert alert-danger" role="alert">
                {formError}
              </div>
            ) : null}

            {lookupsLoading ? (
              <p className="text-secondary">Loading form…</p>
            ) : !lookupErrorMessage ? (
              <ProductForm
                categories={categoriesQuery.data ?? []}
                destinations={destinationsQuery.data ?? []}
                busy={createMutation.isPending}
                serverErrors={serverErrors}
                submitLabel="Create product"
                onSubmit={handleSubmit}
              />
            ) : null}
          </div>
        </div>
      </main>
    </div>
  )
}

export default withAuth(CreateProductPage)
