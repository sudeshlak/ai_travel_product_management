import type { KeyboardEvent } from 'react'
import AiSparkleIcon from '@/view/components/ui/AiSparkleIcon'
import './ProductAiPromptBar.scss'

type ProductAiPromptBarProps = {
  value: string
  disabled?: boolean
  busy?: boolean
  enabled?: boolean
  error?: string
  maxLength?: number
  onChange: (value: string) => void
  onGenerate: () => void
}

function ProductAiPromptBar({
  value,
  disabled = false,
  busy = false,
  enabled = false,
  error,
  maxLength,
  onChange,
  onGenerate,
}: ProductAiPromptBarProps) {
  const buttonTitle = enabled
    ? 'Generate product with AI'
    : 'Enter at least 4 words to use AI'

  function handleKeyDown(event: KeyboardEvent<HTMLInputElement>) {
    if (event.key !== 'Enter') {
      return
    }
    event.preventDefault()
    if (enabled) {
      onGenerate()
    }
  }

  return (
    <div className="product-ai-prompt-bar mb-3">
      <label htmlFor="product-ai-prompt" className="form-label">
        Describe the product
      </label>
      <div className="input-group">
        <input
          id="product-ai-prompt"
          type="text"
          className={`form-control product-ai-prompt-bar__input${error ? ' is-invalid' : ''}`}
          placeholder="e.g. Create a Dinner Buffet at Cinnamon Grand Colombo available until the end of this month."
          value={value}
          disabled={disabled || busy}
          maxLength={maxLength}
          onChange={(event) => onChange(event.target.value)}
          onKeyDown={handleKeyDown}
          autoComplete="off"
        />
        <button
          type="button"
          className="btn btn-primary product-ai-prompt-bar__submit"
          disabled={!enabled}
          aria-label={buttonTitle}
          title={buttonTitle}
          aria-busy={busy}
          onClick={onGenerate}
        >
          <AiSparkleIcon />
        </button>
      </div>
      {busy ? (
        <div className="form-text">Generating…</div>
      ) : (
        <div className="form-text">
          AI will fill name, description, and category. You can edit them after.
        </div>
      )}
      {error ? (
        <div className="invalid-feedback d-block" role="alert">
          {error}
        </div>
      ) : null}
    </div>
  )
}

export default ProductAiPromptBar
