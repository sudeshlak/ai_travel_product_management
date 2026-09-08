import { useState, type FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import {
  ConnectionError,
  UnauthorizedError,
  UnexpectedError,
  ValidationError,
} from '@/api/errors'
import { useAppDispatch, useAppSelector } from '@/store/hooks'
import { clearAuthError, login } from '@/store/slices/authSlice'
import './LoginPage.scss'
import { withGuest } from '../hoc/withGuest'

function LoginPage() {
  const dispatch = useAppDispatch()
  const navigate = useNavigate()
  const { loading, error } = useAppSelector((state) => state.auth)

  const [email, setEmail] = useState<string>('')
  const [password, setPassword] = useState<string>('')

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    dispatch(clearAuthError())
    const result = await dispatch(login({ email, password }))
    if (login.fulfilled.match(result)) {
      navigate('/', { replace: true })
    }
  }

  const fieldErrors =
    error instanceof ValidationError ? error.fields : undefined

  let formError: string | undefined
  if (error instanceof UnauthorizedError) {
    formError = 'Invalid email or password.'
  } else if (error instanceof UnexpectedError) {
    formError = 'Something went wrong. Please try again.'
  } else if (error instanceof ConnectionError) {
    formError = 'Unable to connect. Check your network and try again.'
  }

  return (
    <div className="login-page container py-5">
      <div className="row justify-content-center">
        <div className="col-12 col-sm-10 col-md-6 col-lg-4">
          <h1 className="h3 mb-3">Sign in</h1>
          <p className="text-secondary mb-4">Use your account credentials to continue.</p>

          <form onSubmit={handleSubmit} noValidate>
            <div className="mb-3">
              <label htmlFor="email" className="form-label">
                Email
              </label>
              <input
                id="email"
                type="email"
                className={`form-control${fieldErrors?.email ? ' is-invalid' : ''}`}
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                autoComplete="email"
                required
                disabled={loading}
              />
              {fieldErrors?.email ? (
                <div className="invalid-feedback">{fieldErrors.email}</div>
              ) : null}
            </div>

            <div className="mb-3">
              <label htmlFor="password" className="form-label">
                Password
              </label>
              <input
                id="password"
                type="password"
                className={`form-control${fieldErrors?.password ? ' is-invalid' : ''}`}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                autoComplete="current-password"
                required
                disabled={loading}
              />
              {fieldErrors?.password ? (
                <div className="invalid-feedback">{fieldErrors.password}</div>
              ) : null}
            </div>

            {formError ? (
              <div className="alert alert-danger" role="alert">
                {formError}
              </div>
            ) : null}

            <button type="submit" className="btn btn-primary w-100" disabled={loading}>
              {loading ? 'Signing in…' : 'Sign in'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}

export default withGuest(LoginPage)
