import { useEffect, useState } from 'react'
import { apiRequest } from '../api/client'
import { AuthContext } from './auth-context'

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let ignore = false

    async function checkSession() {
      try {
        const data = await apiRequest('/me')
        if (!ignore) setUser(data.user)
      } catch {
        if (!ignore) setUser(null)
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    checkSession()

    return () => {
      ignore = true
    }
  }, [])

  async function logout() {
    await apiRequest('/logout', {
      method: 'POST',
    })

    setUser(null)
  }

  return (
    <AuthContext value={{ user, setUser, loading, logout }}>
      {children}
    </AuthContext>
  )
}
