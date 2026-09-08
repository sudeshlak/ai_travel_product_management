import { Navigate } from 'react-router-dom'
import { useAppSelector } from '@/store/hooks'

function FallbackRedirect() {
  const isAuthenticated = useAppSelector((state) => state.auth.isAuthenticated)
  return <Navigate to={isAuthenticated ? '/' : '/login'} replace />
}

export default FallbackRedirect
