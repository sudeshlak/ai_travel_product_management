import { useEffect, useState, type FormEvent } from 'react'
import {
  ConnectionError,
  UnauthorizedError,
  ValidationError,
} from '@/api/errors'
import type { ProductCategory, ProductDestination } from '@/types/Product'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { emptyProductFormValues } from '@/types/ProductFormValues'
import { validateProductForm } from '@/service/rules/productFormRules'
import {
  canUseDescriptionAi,
  DESCRIPTION_MAX_LENGTH,
} from '@/service/rules/descriptionAiRules'
import AiSparkleIcon from '@/view/components/ui/AiSparkleIcon'
import FormField from '@/view/components/ui/FormField'
import { useGenerateDescriptionMutation } from '@/view/hooks/useGenerateDescriptionMutation'
import { useProductFormReducer } from '@/view/hooks/useProductFormReducer'
import './ProductForm.scss'

type ProductFormProps = {
  initialValues?: ProductFormValues
  categories: ProductCategory[]
  destinations: ProductDestination[]
  busy?: boolean
  serverErrors?: Record<string, string>
  submitLabel?: string
  onSubmit: (values: ProductFormValues) => void | Promise<void>
}

function ProductForm({
  initialValues = emptyProductFormValues,
  categories,
  destinations,
  busy = false,
  serverErrors,
  submitLabel = 'Save product',
  onSubmit,
}: ProductFormProps) {
  const [state, dispatch] = useProductFormReducer(initialValues)
  const generateMutation = useGenerateDescriptionMutation()
  const [aiError, setAiError] = useState<string | undefined>()

  useEffect(() => {
    if (serverErrors && Object.keys(serverErrors).length > 0) {
      dispatch({ type: 'setErrors', errors: serverErrors })
    }
  }, [serverErrors, dispatch])

  const { values, errors } = state
  const generating = generateMutation.isPending
  const fieldsDisabled = busy || generating
  const aiEnabled = canUseDescriptionAi(values.description) && !fieldsDisabled

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    const nextErrors = validateProductForm(state.values)
    if (Object.keys(nextErrors).length > 0) {
      dispatch({ type: 'setErrors', errors: nextErrors })
      return
    }

    dispatch({ type: 'setErrors', errors: {} })
    await onSubmit(state.values)
  }

  async function handleImproveDescription() {
    setAiError(undefined)

    if (values.description.length > DESCRIPTION_MAX_LENGTH) {
      dispatch({
        type: 'setErrors',
        errors: {
          ...errors,
          description: `Description must be ${DESCRIPTION_MAX_LENGTH} characters or fewer.`,
        },
      })
      return
    }

    if (!canUseDescriptionAi(values.description)) {
      setAiError('Enter at least 4 words to improve the description with AI.')
      return
    }

    const selectedCategory = categories.find(
      (category) => String(category.id) === values.categoryId,
    )

    try {
      const result = await generateMutation.mutateAsync({
        description: values.description,
        productName: values.productName.trim() || undefined,
        category: selectedCategory?.name,
      })
      dispatch({
        type: 'setField',
        field: 'description',
        value: result.description.slice(0, DESCRIPTION_MAX_LENGTH),
      })
    } catch (error) {
      if (error instanceof ValidationError) {
        setAiError(error.fields.description ?? Object.values(error.fields)[0] ?? 'Could not improve the description.')
        return
      }
      if (error instanceof UnauthorizedError) {
        setAiError('Your session expired. Please sign in again.')
        return
      }
      if (error instanceof ConnectionError) {
        setAiError('Unable to connect. Check your network and try again.')
        return
      }
      setAiError('Could not improve the description. Please try again.')
    }
  }

  const aiButtonTitle = aiEnabled
    ? 'Improve description with AI'
    : 'Enter at least 4 words to use AI'

  return (
    <form className="product-form" onSubmit={handleSubmit} noValidate>
      <FormField id="productName" label="Product name" error={errors.productName}>
        <input
          id="productName"
          type="text"
          className={`form-control${errors.productName ? ' is-invalid' : ''}`}
          value={values.productName}
          disabled={fieldsDisabled}
          onChange={(event) =>
            dispatch({
              type: 'setField',
              field: 'productName',
              value: event.target.value,
            })
          }
        />
      </FormField>

      <FormField id="categoryId" label="Category" error={errors.categoryId}>
        <select
          id="categoryId"
          className={`form-select${errors.categoryId ? ' is-invalid' : ''}`}
          value={values.categoryId}
          disabled={fieldsDisabled}
          onChange={(event) =>
            dispatch({
              type: 'setField',
              field: 'categoryId',
              value: event.target.value,
            })
          }
        >
          <option value="">Select a category</option>
          {categories.map((category) => (
            <option key={category.id} value={String(category.id)}>
              {category.name}
            </option>
          ))}
        </select>
      </FormField>

      <fieldset className="mb-3">
        <legend className="form-label mb-2">Destinations</legend>
        <div className={`product-form__destinations${errors.destinationIds ? ' is-invalid' : ''}`}>
          {destinations.length === 0 ? (
            <p className="text-secondary mb-0">No destinations available.</p>
          ) : (
            destinations.map((destination) => {
              const checkboxId = `destination-${destination.id}`
              return (
                <div className="form-check" key={destination.id}>
                  <input
                    id={checkboxId}
                    type="checkbox"
                    className="form-check-input"
                    checked={values.destinationIds.includes(destination.id)}
                    disabled={fieldsDisabled}
                    onChange={() =>
                      dispatch({
                        type: 'toggleDestination',
                        destinationId: destination.id,
                      })
                    }
                  />
                  <label className="form-check-label" htmlFor={checkboxId}>
                    {destination.name}
                  </label>
                </div>
              )
            })
          )}
        </div>
        {errors.destinationIds ? (
          <div className="invalid-feedback d-block">{errors.destinationIds}</div>
        ) : null}
      </fieldset>

      <FormField id="description" label="Description" error={errors.description}>
        <textarea
          id="description"
          className={`form-control${errors.description ? ' is-invalid' : ''}`}
          rows={4}
          maxLength={DESCRIPTION_MAX_LENGTH}
          value={values.description}
          disabled={fieldsDisabled}
          onChange={(event) => {
            setAiError(undefined)
            dispatch({
              type: 'setField',
              field: 'description',
              value: event.target.value,
            })
          }}
        />
        <div className="product-form__description-tools d-flex align-items-center justify-content-between gap-2 mt-2">
          <button
            type="button"
            className="btn btn-sm btn-outline-secondary product-form__ai-btn"
            disabled={!aiEnabled}
            aria-label={aiButtonTitle}
            title={aiButtonTitle}
            aria-busy={generating}
            onClick={() => {
              void handleImproveDescription()
            }}
          >
            <AiSparkleIcon size={16} />
            <span className="product-form__ai-btn-label">
              {generating ? 'Improving…' : 'Improve with AI'}
            </span>
          </button>
          <span className="text-secondary small">
            {values.description.length} / {DESCRIPTION_MAX_LENGTH}
          </span>
        </div>
        {aiError ? (
          <div className="invalid-feedback d-block" role="alert">
            {aiError}
          </div>
        ) : null}
      </FormField>

      <div className="row">
        <div className="col-12 col-sm-6">
          <FormField id="price" label="Price" error={errors.price}>
            <input
              id="price"
              type="number"
              min="0"
              step="0.01"
              className={`form-control${errors.price ? ' is-invalid' : ''}`}
              value={values.price}
              disabled={fieldsDisabled}
              onChange={(event) =>
                dispatch({
                  type: 'setField',
                  field: 'price',
                  value: event.target.value,
                })
              }
            />
          </FormField>
        </div>
        <div className="col-12 col-sm-6">
          <FormField
            id="inventoryCount"
            label="Inventory count"
            error={errors.inventoryCount}
          >
            <input
              id="inventoryCount"
              type="number"
              min="0"
              step="1"
              className={`form-control${errors.inventoryCount ? ' is-invalid' : ''}`}
              value={values.inventoryCount}
              disabled={fieldsDisabled}
              onChange={(event) =>
                dispatch({
                  type: 'setField',
                  field: 'inventoryCount',
                  value: event.target.value,
                })
              }
            />
          </FormField>
        </div>
      </div>

      <div className="row">
        <div className="col-12 col-sm-6">
          <FormField id="validFrom" label="Valid from" error={errors.validFrom}>
            <input
              id="validFrom"
              type="date"
              className={`form-control${errors.validFrom ? ' is-invalid' : ''}`}
              value={values.validFrom}
              disabled={fieldsDisabled}
              onChange={(event) =>
                dispatch({
                  type: 'setField',
                  field: 'validFrom',
                  value: event.target.value,
                })
              }
            />
          </FormField>
        </div>
        <div className="col-12 col-sm-6">
          <FormField id="validUntil" label="Valid until" error={errors.validUntil}>
            <input
              id="validUntil"
              type="date"
              className={`form-control${errors.validUntil ? ' is-invalid' : ''}`}
              value={values.validUntil}
              disabled={fieldsDisabled}
              onChange={(event) =>
                dispatch({
                  type: 'setField',
                  field: 'validUntil',
                  value: event.target.value,
                })
              }
            />
          </FormField>
        </div>
      </div>

      <FormField id="status" label="Status" error={errors.status}>
        <select
          id="status"
          className={`form-select${errors.status ? ' is-invalid' : ''}`}
          value={values.status}
          disabled={fieldsDisabled}
          onChange={(event) =>
            dispatch({
              type: 'setField',
              field: 'status',
              value: event.target.value as ProductFormValues['status'],
            })
          }
        >
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </FormField>

      <button type="submit" className="btn btn-primary w-100" disabled={fieldsDisabled}>
        {busy ? 'Saving…' : submitLabel}
      </button>
    </form>
  )
}

export default ProductForm
