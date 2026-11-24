import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'
import { useAuth } from '../context/auth-context'

function OperatorNotificationsPage() {
  const { user } = useAuth()

  const dashboardPath =
    user?.role === 'super_admin'
      ? '/super-admin/dashboard'
      : '/operator/dashboard'

  const [notifications, setNotifications] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let ignore = false

    async function loadNotifications() {
      try {
        const data = await apiRequest('/notifications')

        if (!ignore) {
          if (!Array.isArray(data.notifications)) {
            throw new Error('Invalid notifications response')
          }

          setNotifications(data.notifications)
        }
      } catch (err) {
        if (!ignore) {
          setError(err.message || 'Failed to load notifications')
        }
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    loadNotifications()

    return () => {
      ignore = true
    }
  }, [])

  return (
    <main className="notifications-page">
      <Link to={dashboardPath}>Back to Dashboard</Link>

      <h1>Notifications</h1>
      <p>View book stock notifications.</p>

      {loading && <p role="status">Loading notifications...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        notifications.length === 0 ? (
          <p>No stock notifications.</p>
        ) : (
          <ul className="notifications-list">
            {notifications.map((notification) => (
              <li key={notification.book_id}>
                <strong>
                  {notification.type === 'out_of_stock'
                    ? 'Out of Stock'
                    : 'Low Stock'}
                </strong>

                <p>{notification.book_title}</p>
                <p>{notification.message}</p>
                <p>Stock: {notification.stock}</p>
              </li>
            ))}
          </ul>
        )
      )}
    </main>
  )
}

export default OperatorNotificationsPage
