import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function NotificationsPage() {
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
      <Link to="/dashboard">Back to Dashboard</Link>

      <h1>Notifications</h1>
      <p>View your library notifications.</p>

      {loading && <p role="status">Loading notifications...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        notifications.length === 0 ? (
          <p>No notifications found.</p>
        ) : (
          <ul className="notifications-list">
            {notifications.map((notification, index) => (
              <li key={`${notification.type}-${notification.loan_id}-${index}`}>
                <p>{notification.message}</p>

                {notification.type === 'overdue_book' && (
                  <p>Days overdue: {notification.days_overdue}</p>
                )}

                {notification.type === 'unpaid_penalty' && (
                  <p>Amount: {notification.amount}</p>
                )}
              </li>
            ))}
          </ul>
        )
      )}
    </main>
  )
}

export default NotificationsPage
