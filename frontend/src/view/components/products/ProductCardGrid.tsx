import type { Product } from '@/types/Product'
import ProductCard from '@/view/components/products/ProductCard'

type ProductCardGridProps = {
  products: Product[]
}

function ProductCardGrid({ products }: ProductCardGridProps) {
  return (
    <div className="row g-3">
      {products.map((product) => (
        <div key={product.id} className="col-12 col-sm-6 col-lg-4">
          <ProductCard product={product} />
        </div>
      ))}
    </div>
  )
}

export default ProductCardGrid
