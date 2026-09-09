import type { ReactNode } from 'react'

type FormFieldProps = {
  id: string
  label: string
  error?: string
  children: ReactNode
}

function FormField({ id, label, error, children }: FormFieldProps) {
  return (
    <div className="mb-3">
      <label htmlFor={id} className="form-label">
        {label}
      </label>
      {children}
      {error ? <div className="invalid-feedback d-block">{error}</div> : null}
    </div>
  )
}

export default FormField
