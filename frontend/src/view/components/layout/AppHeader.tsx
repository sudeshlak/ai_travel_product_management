import { Link } from 'react-router-dom'
import { useAppDispatch, useAppSelector } from '@/store/hooks'
import { logout } from '@/store/slices/authSlice'
import './AppHeader.scss'

export type AppHeaderPrimaryAction = {
  label: string
  to: string
}

type AppHeaderProps = {
  title?: string
  primaryAction?: AppHeaderPrimaryAction | null
}

const defaultPrimaryAction: AppHeaderPrimaryAction = {
  label: 'Product manage',
  to: '/products',
}

function AppHeader({
  title = 'Travel Product Management',
  primaryAction = defaultPrimaryAction,
}: AppHeaderProps) {
  const dispatch = useAppDispatch()
  const isAuthenticated = useAppSelector((state) => state.auth.isAuthenticated)

  return (
    <header className="app-header container py-4">
      <div className="row align-items-center gy-3">
        <div className="col-12 col-md-6">
          <Link to="/" className="app-header__brand text-decoration-none">
            {title}
          </Link>
        </div>
        {isAuthenticated ? (
          <div className="col-12 col-md-6 d-flex flex-wrap justify-content-md-end gap-2">
            {primaryAction ? (
              <Link to={primaryAction.to} className="btn btn-outline-primary">
                {primaryAction.label}
              </Link>
            ) : null}
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => dispatch(logout())}
            >
              Sign out
            </button>
          </div>
        ) : null}
      </div>
    </header>
  )
}

export default AppHeader
