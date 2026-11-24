import { useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { useAuth } from '../context/auth-context'

function StudentDashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function handleLogout() {
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
    <div className="student-layout">
      <aside className="student-sidebar">
        <h2>Library System</h2>
        <nav aria-label="Student navigation">
          <a href="#student-overview" aria-current="page">
            Dashboard
          </a>
          <Link to="/books">Books</Link>
          <Link to="/student/loans">My Loans</Link>
          <Link to="/student/penalties">Penalties</Link>
          <Link to="/student/notifications">Notifications</Link>
        </nav>
      </aside>

      <main className="student-main" id="student-overview">
        <header className="student-header">
          <div>
            <h1>Student Dashboard</h1>
            <p>Welcome, {user.name}!</p>
          </div>
          <button onClick={handleLogout} disabled={loading}>
            {loading ? 'Logging out...' : 'Logout'}
          </button>
        </header>

        {error && <p role="alert">{error}</p>}

        <section aria-label="Student overview">
          <h2>Overview</h2>
          <div className="student-cards">
            <Link
              to="/student/loans"
              style={{ color: 'inherit', textDecoration: 'none' }}
            >
              <article>
                <h3>My Loans</h3>
                <p>View my loans</p>
              </article>
            </Link>

            <Link
              to="/student/penalties"
              style={{ color: 'inherit', textDecoration: 'none' }}
            >
              <article>
                <h3>Penalties</h3>
                <p>View my penalties</p>
              </article>
            </Link>

            <Link
              to="/student/notifications"
              style={{ color: 'inherit', textDecoration: 'none' }}
            >
              <article>
                <h3>Notifications</h3>
                <p>View my notifications</p>
              </article>
            </Link>
          </div>
        </section>
      </main>
    </div>
  )
}

export default StudentDashboard
