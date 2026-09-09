import type { Product } from '@/types/Product'
import './ProductCard.scss'

type ProductCardProps = {
  product: Product
}

function truncate(text: string, maxLength: number): string {
  const trimmed = text.trim()
  if (trimmed.length <= maxLength) {
    return trimmed
  }
  return `${trimmed.slice(0, maxLength).trimEnd()}…`
}

function ProductCard({ product }: ProductCardProps) {
  const destinations =
    product.destinations.length > 0
      ? product.destinations.map((destination) => destination.name).join(', ')
      : '—'

  return (
    <article className="product-card h-100">
      <div className="product-card__body">
        <div className="d-flex justify-content-between align-items-start gap-2 mb-2">
          <h2 className="product-card__title h5 mb-0">{product.productName}</h2>
          <span
            className={`badge ${product.status === 'Active' ? 'text-bg-primary' : 'text-bg-secondary'}`}
          >
            {product.status}
          </span>
        </div>
        <p className="product-card__description mb-3">
          {product.description
            ? truncate(product.description, 140)
            : 'No description available.'}
        </p>
        <dl className="product-card__meta mb-0">
          <div>
            <dt>Price</dt>
            <dd>{product.price}</dd>
          </div>
          <div>
            <dt>Category</dt>
            <dd>{product.category?.name ?? '—'}</dd>
          </div>
          <div>
            <dt>Destinations</dt>
            <dd>{destinations}</dd>
          </div>
          <div>
            <dt>Valid</dt>
            <dd>
              {product.validFrom} – {product.validUntil}
            </dd>
          </div>
        </dl>
      </div>
    </article>
  )
}

export default ProductCard
