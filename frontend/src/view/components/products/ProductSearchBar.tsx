import type { FormEvent } from 'react'
import AiSparkleIcon from '@/view/components/ui/AiSparkleIcon'
import './ProductSearchBar.scss'

type ProductSearchBarProps = {
  value: string
  busy?: boolean
  onChange: (value: string) => void
  onSubmit: () => void
}

function ProductSearchBar({ value, busy = false, onChange, onSubmit }: ProductSearchBarProps) {
  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    onSubmit()
  }

  return (
    <form className="product-search-bar" onSubmit={handleSubmit} role="search">
      <label htmlFor="product-nl-search" className="visually-hidden">
        Search products in natural language
      </label>
      <div className="input-group">
        <input
          id="product-nl-search"
          type="search"
          className="form-control product-search-bar__input"
          placeholder="Describe what you’re looking for…"
          value={value}
          disabled={busy}
          onChange={(event) => onChange(event.target.value)}
          autoComplete="off"
        />
        <button
          type="submit"
          className="btn btn-primary product-search-bar__submit"
          disabled={busy}
          aria-label="Search with AI"
          title="Search with AI"
        >
          <AiSparkleIcon />
        </button>
      </div>
    </form>
  )
}

export default ProductSearchBar
