import { useReducer } from 'react'
import type { ProductFormValues } from '@/types/ProductFormValues'
import { emptyProductFormValues } from '@/types/ProductFormValues'

export type ProductFormState = {
  values: ProductFormValues
  errors: Record<string, string>
}

export type ProductFormAction =
  | { type: 'setField'; field: keyof ProductFormValues; value: ProductFormValues[keyof ProductFormValues] }
  | { type: 'toggleDestination'; destinationId: number }
  | { type: 'setErrors'; errors: Record<string, string> }
  | { type: 'setValues'; values: ProductFormValues }
  | { type: 'reset'; values?: ProductFormValues }

function productFormReducer(
  state: ProductFormState,
  action: ProductFormAction,
): ProductFormState {
  switch (action.type) {
    case 'setField':
      return {
        ...state,
        values: {
          ...state.values,
          [action.field]: action.value,
        },
        errors: {
          ...state.errors,
          [action.field]: '',
        },
      }
    case 'toggleDestination': {
      const selected = state.values.destinationIds.includes(action.destinationId)
      const destinationIds = selected
        ? state.values.destinationIds.filter((id) => id !== action.destinationId)
        : [...state.values.destinationIds, action.destinationId]
      return {
        ...state,
        values: {
          ...state.values,
          destinationIds,
        },
        errors: {
          ...state.errors,
          destinationIds: '',
        },
      }
    }
    case 'setErrors':
      return {
        ...state,
        errors: action.errors,
      }
    case 'setValues':
      return {
        ...state,
        values: action.values,
        errors: {},
      }
    case 'reset':
      return {
        values: action.values ?? emptyProductFormValues,
        errors: {},
      }
    default:
      return state
  }
}

export function useProductFormReducer(initialValues: ProductFormValues = emptyProductFormValues) {
  return useReducer(productFormReducer, {
    values: initialValues,
    errors: {},
  })
}
