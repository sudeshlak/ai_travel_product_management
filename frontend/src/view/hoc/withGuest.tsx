import type { ComponentType } from 'react'
import { Navigate } from 'react-router-dom'
import { useAppSelector } from '@/store/hooks'

export function withGuest<P extends object>(Component: ComponentType<P>) {
  function WithGuestComponent(props: P) {
    const isAuthenticated = useAppSelector((state) => state.auth.isAuthenticated)

    if (isAuthenticated) {
      return <Navigate to="/" replace />
    }

    return <Component {...props} />
  }

  return WithGuestComponent
}
