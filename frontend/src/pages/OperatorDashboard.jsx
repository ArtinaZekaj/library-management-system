import { useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { useAuth } from '../context/auth-context'

function OperatorDashboard() {
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
      setError('Logout failed. Please try again')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="operator-layout">
      <aside className="operator-sidebar">
        <h2>Library System</h2>

        <nav aria-label="Operator navigation">
          <Link to="/operator/dashboard" aria-current="page">
            Dashboard
          </Link>
          <Link to="/books">Books</Link>
          <Link to="/operator/manage-books">Manage Books</Link>
          <span>Loans &amp; Returns</span>
          <span>Notifications</span>
        </nav>
      </aside>

      <main className="operator-main">
        <header className="operator-header">
          <div>
            <h1>Operator Dashboard</h1>
            <p>Welcome, {user.name}!</p>
          </div>

          <button onClick={handleLogout} disabled={loading}>
            {loading ? 'Logging out...' : 'Logout'}
          </button>
        </header>

        {error && <p role="alert">{error}</p>}

        <section aria-label="Operator overview">
          <h2>Overview</h2>

          <div className="operator-cards">
            <article>
              <h3>Manage Books</h3>
              <p>Available soon</p>
            </article>

            <article>
              <h3>Loans &amp; Returns</h3>
              <p>Available soon</p>
            </article>

            <article>
              <h3>Notifications</h3>
              <p>Available soon</p>
            </article>
          </div>
        </section>
      </main>
    </div>
  )
}

export default OperatorDashboard
