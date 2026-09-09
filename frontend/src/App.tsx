import { Route, Routes } from 'react-router-dom'
import CreateProductPage from '@/view/pages/CreateProductPage'
import FallbackRedirect from '@/view/pages/FallbackRedirect'
import HomePage from '@/view/pages/HomePage'
import LoginPage from '@/view/pages/LoginPage'
import ProductEditPage from '@/view/pages/ProductEditPage'
import ProductsPage from '@/view/pages/ProductsPage'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<HomePage />} />
      <Route path="/products" element={<ProductsPage />} />
      <Route path="/products/create" element={<CreateProductPage />} />
      <Route path="/products/:id/edit" element={<ProductEditPage />} />
      <Route path="*" element={<FallbackRedirect />} />
    </Routes>
  )
}

export default App
