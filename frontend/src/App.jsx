import { useState } from 'react'
import { Routes, Route, Navigate, useNavigate } from 'react-router'
import LoginPage from './pages/LoginPage'
import './App.css'
import ProtectedRoute from './components/ProtectedRoute'
import { useAuth } from './context/auth-context'

function Dashboard() {
  const { logout } = useAuth()
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const handleLogout = async () => {
    setError('')
    setLoading(true)

    try {
      await logout()
      navigate('/login', { replace: true })
    } catch {
      setError('Logout failed. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h1>Dashboard Page</h1>
      <button onClick={handleLogout} disabled={loading}>
        {loading ? 'Logging out...' : 'Logout'}
      </button>
      {error && <p role="alert">{error}</p>}
    </div>
  )
}

function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<LoginPage />} />

      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        }
      />

      <Route path="*" element={<h1>Page Not Found</h1>} />
    </Routes>
  )
}

export default App
