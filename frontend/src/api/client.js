export async function apiRequest(endpoint, options = {}) {
  const response = await fetch(`/api${endpoint}`, {
    ...options,
    credentials: 'include',
  })

  const data = await response.json()

  if (!response.ok) {
    const error = new Error(data.message || 'Request failed')
    error.status = response.status
    throw error
  }

  return data
}
