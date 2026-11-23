
import { useState } from 'react'
import { useNavigate } from 'react-router'
import { apiRequest } from '../api/client'
import { useAuth } from '../context/auth-context'

function LoginPage() {
    const navigate = useNavigate()
    const { setUser } = useAuth()

    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [message, setMessage] = useState('')
    const [loading, setLoading] = useState(false)

    const handleLogin = async (e) => {
        e.preventDefault()
        setMessage('')
        setLoading(true)

        try {
            await apiRequest('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            })

            const data = await apiRequest('/me')
            setUser(data.user)
            navigate('/dashboard', { replace: true })
        } catch (error) {
            setMessage(
                error.status === 401
                    ? 'Invalid email or password'
                    : error.message || 'Server error'
            )
        } finally {
            setLoading(false)
        }
    }

    return (
        <section className="login-page">
            <div className="auth">
                <h1>Library Management System</h1>
                <h2>Login</h2>

                <form onSubmit={handleLogin}>
                    <label htmlFor="email">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Enter email"
                        autoComplete="username"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />

                    <label htmlFor="password">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter password"
                        autoComplete="current-password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />

                    <button type="submit" disabled={loading}>
                        {loading ? 'Logging in...' : 'Login'}
                    </button>

                    {message && <p role="alert">{message}</p>}
                </form>
            </div>
        </section>
    )
}

export default LoginPage
