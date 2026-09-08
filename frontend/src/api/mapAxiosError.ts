import { isAxiosError } from 'axios'
import {
  ConnectionError,
  UnauthorizedError,
  UnexpectedError,
  ValidationError,
} from '@/api/errors'

/** Maps an axios (or unknown) failure to one of the four frontend error classes, then throws. */
export function mapAxiosError(error: unknown): never {
  if (!isAxiosError(error) || !error.response) {
    throw new ConnectionError()
  }

  const { status, data } = error.response

  if (status === 401) {
    throw new UnauthorizedError()
  }

  if (status === 422) {
    const fields = Array.isArray(data)
      ? (Object.assign({}, ...data) as Record<string, string>)
      : {}
    throw new ValidationError(fields)
  }

  throw new UnexpectedError()
}
