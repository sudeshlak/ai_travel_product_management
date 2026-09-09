import { useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import {
  ConnectionError,
  UnauthorizedError,
  UnexpectedError,
  ValidationError,
} from '@/api/errors'
import { mapServerErrors } from '@/service/mappers/productFormErrorMapper'
import { mapProductToFormValues } from '@/service/mappers/productMapper'
import type { ProductFormValues } from '@/types/ProductFormValues'
import AppHeader from '@/view/components/layout/AppHeader'
import ProductForm from '@/view/components/products/ProductForm'
import { withAuth } from '@/view/hoc/withAuth'
import { useCategoriesQuery } from '@/view/hooks/useCategoriesQuery'
import { useDestinationsQuery } from '@/view/hooks/useDestinationsQuery'
import { useProductQuery } from '@/view/hooks/useProductQuery'
import { useUpdateProductMutation } from '@/view/hooks/useUpdateProductMutation'
import './ProductEditPage.scss'

function parseProductId(raw: string | undefined): number | null {
  if (!raw) {
    return null
  }
  const id = Number(raw)
  return Number.isInteger(id) && id > 0 ? id : null
}

function ProductEditPage() {
  const navigate = useNavigate()
  const { id: rawId } = useParams<{ id: string }>()
  const productId = parseProductId(rawId)

  const productQuery = useProductQuery(productId)
  const categoriesQuery = useCategoriesQuery()
  const destinationsQuery = useDestinationsQuery()
  const updateMutation = useUpdateProductMutation()

  const [serverErrors, setServerErrors] = useState<Record<string, string>>({})
  const [formError, setFormError] = useState<string | undefined>()

  const pageLoading =
    productId !== null &&
    (productQuery.isLoading || categoriesQuery.isLoading || destinationsQuery.isLoading)

  const loadError =
    productId === null
      ? undefined
      : (productQuery.error ?? categoriesQuery.error ?? destinationsQuery.error)

  const loadErrorMessage = useMemo(() => {
    if (productId === null) {
      return 'Invalid product id.'
    }
    if (!loadError) {
      return undefined
    }
    if (loadError instanceof UnauthorizedError) {
      return 'Your session expired. Please sign in again.'
    }
    if (loadError instanceof ConnectionError) {
      return 'Unable to connect. Check your network and try again.'
    }
    if (loadError instanceof UnexpectedError) {
      return 'Product not found or something went wrong loading it.'
    }
    return 'Something went wrong loading the product.'
  }, [loadError, productId])

  async function handleSubmit(values: ProductFormValues) {
    if (productId === null) {
      return
    }

    setFormError(undefined)
    setServerErrors({})
    try {
      await updateMutation.mutateAsync({ id: productId, values })
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
      setFormError('Could not update the product. Please try again.')
    }
  }

  const product = productQuery.data
  const canShowForm =
    productId !== null &&
    !pageLoading &&
    !loadErrorMessage &&
    product !== undefined

  return (
    <div className="product-edit-page">
      <AppHeader primaryAction={{ label: 'Back to products', to: '/products' }} />
      <main className="container pb-4">
        <div className="row justify-content-center">
          <div className="col-12 col-md-8 col-lg-6">
            <h1 className="h3 mb-2">Update product</h1>
            <p className="text-secondary mb-4">
              Edit an existing travel product you own.
            </p>

            {loadErrorMessage ? (
              <div className="alert alert-danger" role="alert">
                <p className="mb-2">{loadErrorMessage}</p>
                <Link to="/products" className="alert-link">
                  Back to products
                </Link>
              </div>
            ) : null}

            {formError ? (
              <div className="alert alert-danger" role="alert">
                {formError}
              </div>
            ) : null}

            {pageLoading ? <p className="text-secondary">Loading form…</p> : null}

            {canShowForm ? (
              <ProductForm
                key={product.id}
                initialValues={mapProductToFormValues(product)}
                categories={categoriesQuery.data ?? []}
                destinations={destinationsQuery.data ?? []}
                busy={updateMutation.isPending}
                serverErrors={serverErrors}
                submitLabel="Update product"
                onSubmit={handleSubmit}
              />
            ) : null}
          </div>
        </div>
      </main>
    </div>
  )
}

export default withAuth(ProductEditPage)
