import { useParams } from 'react-router-dom'
import AppHeader from '@/view/components/layout/AppHeader'
import { withAuth } from '@/view/hoc/withAuth'
import './ProductEditPage.scss'

function ProductEditPage() {
  const { id } = useParams<{ id: string }>()

  return (
    <div className="product-edit-page">
      <AppHeader />
      <main className="container pb-4">
        <h1 className="h3 mb-2">Update product</h1>
        <p className="text-secondary mb-0">
          Update form coming soon{id ? ` (product #${id})` : ''}.
        </p>
      </main>
    </div>
  )
}

export default withAuth(ProductEditPage)
