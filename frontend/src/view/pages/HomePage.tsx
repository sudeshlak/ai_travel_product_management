import { useMemo, useState } from 'react'
import {
  ConnectionError,
  UnauthorizedError,
  UnexpectedError,
} from '@/api/errors'
import AppHeader from '@/view/components/layout/AppHeader'
import ProductCardGrid from '@/view/components/products/ProductCardGrid'
import ProductSearchBar from '@/view/components/products/ProductSearchBar'
import { useProductSearchInfiniteQuery } from '@/view/hooks/useProductSearchInfiniteQuery'
import './HomePage.scss'

function HomePage() {
  const [draftQuery, setDraftQuery] = useState('')
  const [submittedQuery, setSubmittedQuery] = useState('')

  const searchQuery = useProductSearchInfiniteQuery(submittedQuery)

  const products = useMemo(
    () => searchQuery.data?.pages.flatMap((page) => page.items) ?? [],
    [searchQuery.data],
  )

  const total = searchQuery.data?.pages[0]?.total ?? 0

  const errorMessage = useMemo(() => {
    if (!searchQuery.isError) {
      return undefined
    }
    const error = searchQuery.error
    if (error instanceof UnauthorizedError) {
      return 'Your session expired. Please sign in again.'
    }
    if (error instanceof ConnectionError) {
      return 'Unable to connect. Check your network and try again.'
    }
    if (error instanceof UnexpectedError) {
      return 'Something went wrong loading products.'
    }
    return 'Something went wrong loading products.'
  }, [searchQuery.error, searchQuery.isError])

  function handleSearchSubmit() {
    setSubmittedQuery(draftQuery.trim())
  }

  const isInitialLoading = searchQuery.isLoading
  const isFetchingMore = searchQuery.isFetchingNextPage
  const showMoreVisible = Boolean(searchQuery.hasNextPage)

  return (
    <div className="home-page">
      <AppHeader />
      <main className="container pb-4">
        <div className="row mb-4">
          <div className="col-12 col-lg-8">
            <h1 className="h3 mb-2">Discover travel products</h1>
            <p className="lead mb-3">
              Search with natural language, or browse what’s available.
            </p>
            <ProductSearchBar
              value={draftQuery}
              busy={searchQuery.isFetching && !isFetchingMore}
              onChange={setDraftQuery}
              onSubmit={handleSearchSubmit}
            />
          </div>
        </div>

        {errorMessage ? (
          <div className="alert alert-danger" role="alert">
            {errorMessage}
          </div>
        ) : null}

        {isInitialLoading ? (
          <p className="text-secondary">Loading products…</p>
        ) : null}

        {!isInitialLoading && !errorMessage && products.length === 0 ? (
          <p className="text-secondary">No products matched your search.</p>
        ) : null}

        {!isInitialLoading && products.length > 0 ? (
          <>
            <p className="text-secondary mb-3">
              Showing {products.length}
              {total > 0 ? ` of ${total}` : ''} products
              {submittedQuery ? ` for “${submittedQuery}”` : ''}.
            </p>
            <ProductCardGrid products={products} />
          </>
        ) : null}

        {showMoreVisible ? (
          <div className="d-flex justify-content-center mt-4">
            <button
              type="button"
              className="btn btn-outline-primary px-4"
              disabled={isFetchingMore}
              onClick={() => {
                void searchQuery.fetchNextPage()
              }}
            >
              {isFetchingMore ? 'Loading…' : 'Show more'}
            </button>
          </div>
        ) : null}
      </main>
    </div>
  )
}

export default HomePage
