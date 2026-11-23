import { useState } from 'react'
import { Routes, Route, Navigate, useNavigate } from 'react-router'
import LoginPage from './pages/LoginPage'
import './App.css'
import ProtectedRoute from './components/ProtectedRoute'
import { useAuth } from './context/auth-context'
import StudentDashboard from './pages/StudentDashboard'
import BooksPage from './pages/BooksPage'
import MyLoansPage from './pages/MyLoansPage'
import PenaltiesPage from './pages/PenaltiesPage'

function Dashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  if (user?.role === 'student') {
    return <Navigate to="/student/dashboard" replace />
  }

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

      <Route
        path="/student/dashboard"
        element={
          <ProtectedRoute allowedRoles={['student']}>
            <StudentDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/books"
        element={
          <ProtectedRoute>
            <BooksPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/student/loans"
        element={
          <ProtectedRoute allowedRoles={['student']}>
            <MyLoansPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/student/penalties"
        element={
          <ProtectedRoute allowedRoles={['student']}>
            <PenaltiesPage />
          </ProtectedRoute>
        }
      />

      <Route path="*" element={<h1>Page Not Found</h1>} />
    </Routes>
  )
}

export default App
