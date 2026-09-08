import type { ComponentType } from 'react'
import { Navigate } from 'react-router-dom'
import { useAppSelector } from '@/store/hooks'

export function withAuth<P extends object>(Component: ComponentType<P>) {
  function WithAuthComponent(props: P) {
    const isAuthenticated = useAppSelector((state) => state.auth.isAuthenticated)

    if (!isAuthenticated) {
      return <Navigate to="/login" replace />
    }

    return <Component {...props} />
  }

  return WithAuthComponent
}
