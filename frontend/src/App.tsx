import { Route, Routes } from 'react-router-dom'
import FallbackRedirect from '@/view/pages/FallbackRedirect'
import HomePage from '@/view/pages/HomePage'
import LoginPage from '@/view/pages/LoginPage'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<HomePage />} />
      <Route path="*" element={<FallbackRedirect />} />
    </Routes>
  )
}

export default App
