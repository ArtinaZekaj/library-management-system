import { useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { useAuth } from '../context/auth-context'

function SuperAdminDashboard() {
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

                <nav aria-label="Super Admin navigation">
                    <Link to="/super-admin/dashboard" aria-current="page">
                        Dashboard
                    </Link>
                    <Link to="/books">Books</Link>
                    <Link to="/operator/manage-books">Manage Books</Link>
                    <Link to="/operator/loans">Loans &amp; Returns</Link>
                    <Link to="/operator/notifications">Notifications</Link>
                    <Link to="/super-admin/users">User Management</Link>
                </nav>
            </aside>

            <main className="operator-main">
                <header className="operator-header">
                    <div>
                        <h1>Super Admin Dashboard</h1>
                        <p>Welcome, {user.name}!</p>
                    </div>

                    <button onClick={handleLogout} disabled={loading}>
                        {loading ? 'Logging out...' : 'Logout'}
                    </button>
                </header>

                {error && <p role="alert">{error}</p>}

                <section aria-label="Super Admin overview">
                    <h2>Overview</h2>

                    <div className="operator-cards">
                        <Link
                            to="/super-admin/users"
                            style={{ color: 'inherit', textDecoration: 'none' }}
                        >
                            <article>
                                <h3>User Management</h3>
                                <p>Manage students and operators</p>
                            </article>
                        </Link>

                        <Link
                            to="/operator/manage-books"
                            style={{ color: 'inherit', textDecoration: 'none' }}
                        >
                            <article>
                                <h3>Manage Books</h3>
                                <p>View and manage books</p>
                            </article>
                        </Link>

                        <Link
                            to="/operator/loans"
                            style={{ color: 'inherit', textDecoration: 'none' }}
                        >
                            <article>
                                <h3>Loans &amp; Returns</h3>
                                <p>View loans and returns</p>
                            </article>
                        </Link>

                        <Link
                            to="/operator/notifications"
                            style={{ color: 'inherit', textDecoration: 'none' }}
                        >
                            <article>
                                <h3>Notifications</h3>
                                <p>View stock notifications</p>
                            </article>
                        </Link>
                    </div>
                </section>
            </main>
        </div>
    )
}

export default SuperAdminDashboard
