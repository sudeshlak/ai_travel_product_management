import { useEffect, useState, type FormEvent } from 'react'
import {
  ConnectionError,
  UnauthorizedError,
  ValidationError,
} from '@/api/errors'
import type { ProductCategory, ProductDestination } from '@/types/Product'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { emptyProductFormValues } from '@/types/ProductFormValues'
import { DESCRIPTION_MAX_LENGTH } from '@/service/rules/descriptionAiRules'
import {
  canUseProductAiPrompt,
  PRODUCT_AI_PROMPT_MAX_LENGTH,
} from '@/service/rules/productAiPromptRules'
import { validateProductForm } from '@/service/rules/productFormRules'
import ProductAiPromptBar from '@/view/components/products/ProductAiPromptBar'
import FormField from '@/view/components/ui/FormField'
import { useGenerateProductMutation } from '@/view/hooks/useGenerateProductMutation'
import { useProductFormReducer } from '@/view/hooks/useProductFormReducer'
import './ProductForm.scss'

type ProductFormProps = {
  initialValues?: ProductFormValues
  categories: ProductCategory[]
  destinations: ProductDestination[]
  busy?: boolean
  serverErrors?: Record<string, string>
  submitLabel?: string
  showAiPrompt?: boolean
  onSubmit: (values: ProductFormValues) => void | Promise<void>
}

function ProductForm({
  initialValues = emptyProductFormValues,
  categories,
  destinations,
  busy = false,
  serverErrors,
  submitLabel = 'Save product',
  showAiPrompt = false,
  onSubmit,
}: ProductFormProps) {
  const [state, dispatch] = useProductFormReducer(initialValues)
  const generateMutation = useGenerateProductMutation()
  const [prompt, setPrompt] = useState('')
  const [aiError, setAiError] = useState<string | undefined>()

  useEffect(() => {
    if (serverErrors && Object.keys(serverErrors).length > 0) {
      dispatch({ type: 'setErrors', errors: serverErrors })
    }
  }, [serverErrors, dispatch])

  const { values, errors } = state
  const generating = showAiPrompt && generateMutation.isPending
  const fieldsDisabled = busy || generating
  const promptEnabled = canUseProductAiPrompt(prompt) && !fieldsDisabled

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

  async function handleGenerateProduct() {
    setAiError(undefined)

    if (!canUseProductAiPrompt(prompt)) {
      setAiError('Enter at least 4 words to generate a product with AI.')
      return
    }

    try {
      const result = await generateMutation.mutateAsync(prompt)
      const matchedCategory = categories.some(
        (category) => category.id === result.categoryId,
      )
      dispatch({
        type: 'patchFields',
        fields: {
          productName: result.productName,
          description: result.description.slice(0, DESCRIPTION_MAX_LENGTH),
          categoryId: matchedCategory ? String(result.categoryId) : '',
        },
      })
      if (!matchedCategory) {
        setAiError('Could not match a category. Please choose one.')
      }
    } catch (error) {
      if (error instanceof ValidationError) {
        setAiError(
          error.fields.prompt ??
            Object.values(error.fields)[0] ??
            'Could not generate the product.',
        )
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
      setAiError('Could not generate the product. Please try again.')
    }
  }

  return (
    <form className="product-form" onSubmit={handleSubmit} noValidate>
      {showAiPrompt ? (
        <ProductAiPromptBar
          value={prompt}
          disabled={fieldsDisabled}
          busy={generating}
          enabled={promptEnabled}
          error={aiError}
          maxLength={PRODUCT_AI_PROMPT_MAX_LENGTH}
          onChange={(value) => {
            setAiError(undefined)
            setPrompt(value)
          }}
          onGenerate={() => {
            void handleGenerateProduct()
          }}
        />
      ) : null}

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
          onChange={(event) =>
            dispatch({
              type: 'setField',
              field: 'description',
              value: event.target.value,
            })
          }
        />
        <div className="d-flex justify-content-end mt-2">
          <span className="text-secondary small">
            {values.description.length} / {DESCRIPTION_MAX_LENGTH}
          </span>
        </div>
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
