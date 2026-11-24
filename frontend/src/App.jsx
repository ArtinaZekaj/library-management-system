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
import NotificationsPage from './pages/NotificationsPage'
import OperatorDashboard from './pages/OperatorDashboard'
import ManageBooksPage from './pages/ManageBooksPage'
import OperatorLoansPage from './pages/OperatorLoansPage'
import OperatorNotificationsPage from './pages/OperatorNotificationsPage'
import SuperAdminDashboard from './pages/SuperAdminDashboard'
import UserManagementPage from './pages/UserManagementPage'
import SuperAdminSettingsPage from './pages/SuperAdminSettingsPage'
import SystemLogsPage from './pages/SystemLogsPage'

function Dashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  if (user?.role === 'student') {
    return <Navigate to="/student/dashboard" replace />
  }

  if (user?.role === 'operator') {
    return <Navigate to="/operator/dashboard" replace />
  }

  if (user?.role === 'super_admin') {
    return <Navigate to="/super-admin/dashboard" replace />
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

      <Route
        path="/student/notifications"
        element={
          <ProtectedRoute allowedRoles={['student']}>
            <NotificationsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/operator/dashboard"
        element={
          <ProtectedRoute allowedRoles={['operator', 'super_admin']}>
            <OperatorDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/operator/manage-books"
        element={
          <ProtectedRoute allowedRoles={['operator', 'super_admin']}>
            <ManageBooksPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/operator/loans"
        element={
          <ProtectedRoute allowedRoles={['operator', 'super_admin']}>
            <OperatorLoansPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/operator/notifications"
        element={
          <ProtectedRoute allowedRoles={['operator', 'super_admin']}>
            <OperatorNotificationsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/super-admin/dashboard"
        element={
          <ProtectedRoute allowedRoles={['super_admin']}>
            <SuperAdminDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/super-admin/users"
        element={
          <ProtectedRoute allowedRoles={['super_admin']}>
            <UserManagementPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/super-admin/settings"
        element={
          <ProtectedRoute allowedRoles={['super_admin']}>
            <SuperAdminSettingsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/super-admin/system-logs"
        element={
          <ProtectedRoute allowedRoles={['super_admin']}>
            <SystemLogsPage />
          </ProtectedRoute>
        }
      />
      <Route path="*" element={<h1>Page Not Found</h1>} />
    </Routes>
  )
}

export default App
