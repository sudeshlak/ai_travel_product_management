import './ProductSummaryStats.scss'

type ProductSummaryStatsProps = {
  totalProducts: number
  activeProducts: number
  expiredProducts: number
}

function ProductSummaryStats({
  totalProducts,
  activeProducts,
  expiredProducts,
}: ProductSummaryStatsProps) {
  return (
    <div className="product-summary-stats row g-3 mb-4">
      <div className="col-12 col-md-4">
        <div className="product-summary-stats__item">
          <p className="product-summary-stats__label mb-1">Total Products</p>
          <p className="product-summary-stats__value mb-0">{totalProducts}</p>
        </div>
      </div>
      <div className="col-12 col-md-4">
        <div className="product-summary-stats__item">
          <p className="product-summary-stats__label mb-1">Active Products</p>
          <p className="product-summary-stats__value mb-0">{activeProducts}</p>
        </div>
      </div>
      <div className="col-12 col-md-4">
        <div className="product-summary-stats__item">
          <p className="product-summary-stats__label mb-1">Expired Products</p>
          <p className="product-summary-stats__value mb-0">{expiredProducts}</p>
        </div>
      </div>
    </div>
  )
}

export default ProductSummaryStats
