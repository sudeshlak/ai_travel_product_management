import { useAppSelector } from '@/store/hooks'
import AppHeader from '@/view/components/layout/AppHeader'
import { withAuth } from '@/view/hoc/withAuth'
import './HomePage.scss'

function HomePage() {
  const user = useAppSelector((state) => state.auth.user)

  return (
    <div className="home-page">
      <AppHeader />
      <main className="container pb-4">
        <h1 className="h3 mb-2">Welcome</h1>
        <p className="lead mb-0">
          {user ? `Signed in as ${user.name}.` : 'Plan and manage travel products.'}
        </p>
      </main>
    </div>
  )
}

export default withAuth(HomePage)
