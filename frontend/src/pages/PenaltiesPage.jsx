import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function PenaltiesPage() {
  const [penalties, setPenalties] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let ignore = false

    async function loadPenalties() {
      try {
        const data = await apiRequest('/loans')

        if (!ignore) {
          if (!Array.isArray(data.loans)) {
            throw new Error('Invalid loans response')
          }

          const result = data.loans.flatMap((loan) =>
            loan.penalties.map((penalty) => ({
              ...penalty,
              book_title: loan.book_title,
            }))
          )

          setPenalties(result)
        }
      } catch (err) {
        if (!ignore) {
          setError(err.message || 'Failed to load penalties')
        }
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    loadPenalties()

    return () => {
      ignore = true
    }
  }, [])

  return (
    <main className="penalties-page">
      <Link to="/dashboard">Back to Dashboard</Link>

      <h1>Penalties</h1>
      <p>View your library penalties.</p>

      {loading && <p role="status">Loading penalties...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        penalties.length === 0 ? (
          <p>No penalties found.</p>
        ) : (
          <div className="penalties-table-wrapper">
            <table className="penalties-table">
              <thead>
                <tr>
                  <th>Book</th>
                  <th>Amount</th>
                  <th>Days Late</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {penalties.map((penalty) => (
                  <tr key={penalty.id}>
                    <td>{penalty.book_title}</td>
                    <td>{penalty.amount}</td>
                    <td>{penalty.days_late}</td>
                    <td>
                      {penalty.paid ? 'Paid' : 'Unpaid'}
                    </td>
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

export default PenaltiesPage
