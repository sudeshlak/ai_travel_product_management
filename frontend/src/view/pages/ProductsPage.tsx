import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import {
  ConnectionError,
  UnexpectedError,
  UnauthorizedError,
} from '@/api/errors'
import type { Product } from '@/types/Product'
import AppHeader from '@/view/components/layout/AppHeader'
import ConfirmDialog from '@/view/components/feedback/ConfirmDialog'
import ProductSummaryStats from '@/view/components/products/ProductSummaryStats'
import ProductsTable from '@/view/components/products/ProductsTable'
import { withAuth } from '@/view/hoc/withAuth'
import { useDeleteProductMutation } from '@/view/hooks/useDeleteProductMutation'
import { useProductsQuery } from '@/view/hooks/useProductsQuery'
import { useProductSummaryQuery } from '@/view/hooks/useProductSummaryQuery'
import './ProductsPage.scss'

function ProductsPage() {
  const navigate = useNavigate()
  const [page, setPage] = useState(1)
  const [productToDelete, setProductToDelete] = useState<Product | null>(null)

  const { data, isLoading, isFetching, error, isError } = useProductsQuery(page)
  const summaryQuery = useProductSummaryQuery()
  const deleteMutation = useDeleteProductMutation()

  async function handleConfirmDelete() {
    if (!productToDelete) {
      return
    }

    try {
      await deleteMutation.mutateAsync(productToDelete.id)
      setProductToDelete(null)
      if (data && data.items.length === 1 && page > 1) {
        setPage(page - 1)
      }
    } catch {
      // Error surfaced via deleteMutation.error below
    }
  }

  let listErrorMessage: string | undefined
  if (isError) {
    if (error instanceof UnauthorizedError) {
      listErrorMessage = 'Your session expired. Please sign in again.'
    } else if (error instanceof ConnectionError) {
      listErrorMessage = 'Unable to connect. Check your network and try again.'
    } else if (error instanceof UnexpectedError) {
      listErrorMessage = 'Something went wrong loading products.'
    } else {
      listErrorMessage = 'Something went wrong loading products.'
    }
  }

  let summaryErrorMessage: string | undefined
  if (summaryQuery.isError) {
    if (summaryQuery.error instanceof UnauthorizedError) {
      summaryErrorMessage = 'Your session expired. Please sign in again.'
    } else if (summaryQuery.error instanceof ConnectionError) {
      summaryErrorMessage = 'Unable to connect. Check your network and try again.'
    } else {
      summaryErrorMessage = 'Something went wrong loading product summary.'
    }
  }

  let deleteErrorMessage: string | undefined
  if (deleteMutation.isError) {
    if (deleteMutation.error instanceof ConnectionError) {
      deleteErrorMessage = 'Unable to connect. Check your network and try again.'
    } else {
      deleteErrorMessage = 'Could not delete the product. Please try again.'
    }
  }

  const pageCount = data?.pageCount ?? 1
  const canGoPrev = page > 1
  const canGoNext = page < pageCount

  return (
    <div className="products-page">
      <AppHeader
        primaryAction={{ label: 'Create product', to: '/products/create' }}
      />
      <main className="container pb-4">
        <div className="row mb-3">
          <div className="col-12">
            <h1 className="h3 mb-1">Product dashboard</h1>
            <p className="text-secondary mb-0">Overview of the travel products you own.</p>
          </div>
        </div>

        {summaryErrorMessage ? (
          <div className="alert alert-danger" role="alert">
            {summaryErrorMessage}
          </div>
        ) : null}

        {summaryQuery.isLoading ? (
          <p className="text-secondary mb-4">Loading summary…</p>
        ) : summaryQuery.data ? (
          <ProductSummaryStats
            totalProducts={summaryQuery.data.totalProducts}
            activeProducts={summaryQuery.data.activeProducts}
            expiredProducts={summaryQuery.data.expiredProducts}
          />
        ) : null}

        {listErrorMessage ? (
          <div className="alert alert-danger" role="alert">
            {listErrorMessage}
          </div>
        ) : null}

        {deleteErrorMessage ? (
          <div className="alert alert-danger" role="alert">
            {deleteErrorMessage}
          </div>
        ) : null}

        {isLoading ? (
          <p className="text-secondary">Loading products…</p>
        ) : (
          <ProductsTable
            products={data?.items ?? []}
            page={data?.page ?? page}
            perPage={data?.perPage}
            onEdit={(product) => navigate(`/products/${product.id}/edit`)}
            onDelete={(product) => {
              deleteMutation.reset()
              setProductToDelete(product)
            }}
          />
        )}

        <div className="row align-items-center mt-3 gy-2">
          <div className="col-12 col-sm-6 text-secondary">
            {data ? (
              <span>
                Page {data.page} of {data.pageCount} ({data.total} total)
                {isFetching && !isLoading ? ' · Updating…' : ''}
              </span>
            ) : null}
          </div>
          <div className="col-12 col-sm-6 d-flex justify-content-sm-end gap-2">
            <button
              type="button"
              className="btn btn-outline-primary"
              disabled={!canGoPrev || isFetching}
              onClick={() => setPage((current) => Math.max(1, current - 1))}
            >
              Previous
            </button>
            <button
              type="button"
              className="btn btn-outline-primary"
              disabled={!canGoNext || isFetching}
              onClick={() => setPage((current) => current + 1)}
            >
              Next
            </button>
          </div>
        </div>
      </main>

      <ConfirmDialog
        open={productToDelete !== null}
        title="Delete product"
        message={
          productToDelete
            ? `Delete “${productToDelete.productName}”? This cannot be undone.`
            : ''
        }
        confirmLabel="Delete"
        busy={deleteMutation.isPending}
        onCancel={() => {
          if (!deleteMutation.isPending) {
            setProductToDelete(null)
          }
        }}
        onConfirm={() => {
          void handleConfirmDelete()
        }}
      />
    </div>
  )
}

export default withAuth(ProductsPage)
