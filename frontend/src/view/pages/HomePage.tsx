import { useAppDispatch, useAppSelector } from '@/store/hooks'
import { logout } from '@/store/slices/authSlice'
import './HomePage.scss'
import { withAuth } from '../hoc/withAuth'

function HomePage() {
  const dispatch = useAppDispatch()
  const user = useAppSelector((state) => state.auth.user)

  return (
    <div className="home-page container py-4">
      <div className="row align-items-center">
        <div className="col-12 col-md-8">
          <h1>Travel Product Management</h1>
          <p className="lead mb-0">
            {user ? `Signed in as ${user.name}.` : 'Plan and manage travel products.'}
          </p>
        </div>
        <div className="col-12 col-md-4 mt-3 mt-md-0 text-md-end">
          <button
            type="button"
            className="btn btn-outline-secondary"
            onClick={() => dispatch(logout())}
          >
            Sign out
          </button>
        </div>
      </div>
    </div>
  )
}

export default withAuth(HomePage)
