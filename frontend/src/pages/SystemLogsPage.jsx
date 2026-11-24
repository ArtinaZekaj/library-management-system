import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function SystemLogsPage() {
  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let ignore = false

    async function loadLogs() {
      try {
        const data = await apiRequest('/system-logs')

        if (!Array.isArray(data.logs)) {
          throw new Error('Invalid system logs response')
        }

        if (!ignore) {
          setLogs(data.logs)
        }
      } catch (err) {
        if (!ignore) {
          setError(err.message || 'Failed to load system logs')
        }
      } finally {
        if (!ignore) {
          setLoading(false)
        }
      }
    }

    loadLogs()

    return () => {
      ignore = true
    }
  }, [])

  return (
    <main className="manage-books-page">
      <Link to="/super-admin/dashboard">
        Back to Dashboard
      </Link>

      <h1>System Logs</h1>
      <p>View recorded activities in the system.</p>

      {loading && <p role="status">Loading system logs...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        logs.length === 0 ? (
          <p>No system logs found.</p>
        ) : (
          <div className="manage-books-table-wrapper">
            <table className="manage-books-table">
              <thead>
                <tr>
                  <th>Actor ID</th>
                  <th>Action</th>
                  <th>Entity</th>
                  <th>Entity ID</th>
                  <th>Date</th>
                  <th>Details</th>
                </tr>
              </thead>

              <tbody>
                {logs.map((log, index) => (
                  <tr key={index}>
                    <td>{log.actor_id}</td>
                    <td>{log.action_type}</td>
                    <td>{log.entity_type}</td>
                    <td>{log.entity_id}</td>
                    <td>
                      {log.timestamp
                        ? new Date(log.timestamp).toLocaleString()
                        : '-'}
                    </td>
                    <td>{log.details || '-'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )
      )}
    </main>
  )
}

export default SystemLogsPage
