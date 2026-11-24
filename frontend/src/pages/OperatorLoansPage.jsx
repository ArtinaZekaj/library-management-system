import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function formatDate(value) {
  if (!value) return '-'

  const date = new Date(value)
  return Number.isNaN(date.getTime())
    ? '-'
    : date.toLocaleDateString()
}

function OperatorLoansPage() {
  const [loans, setLoans] = useState([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [returningId, setReturningId] = useState(null)

  useEffect(() => {
    let ignore = false

    async function loadLoans() {
      try {
        const data = await apiRequest('/loans')

        if (!ignore) {
          if (!Array.isArray(data.loans)) {
            throw new Error('Invalid loans response')
          }

          setLoans(data.loans)
        }
      } catch (err) {
        if (!ignore) {
          setLoadError(err.message || 'Failed to load loans')
        }
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    loadLoans()

    return () => {
      ignore = true
    }
  }, [])

  async function handleReturn(loan) {
    if (returningId !== null) return

    const confirmed = window.confirm(
      `Are you sure you want to return "${loan.book_title}"?`
    )

    if (!confirmed) return

    setError('')
    setMessage('')
    setReturningId(loan.id)

    try {
      await apiRequest(`/loans/${loan.id}/return`, {
        method: 'POST',
      })

      const data = await apiRequest('/loans')

      if (!Array.isArray(data.loans)) {
        throw new Error('Invalid loans response')
      }

      setLoans(data.loans)
      setMessage('Book returned successfully.')
    } catch (err) {
      setError(err.message || 'Unable to update loan. Refresh the page.')
    } finally {
      setReturningId(null)
    }
  }

  const activeLoans = loans.filter(
    (loan) => loan.returned_at === null
  )

  const returnedLoans = loans.filter(
    (loan) => loan.returned_at !== null
  )

  return (
    <main className="loans-page">
      <Link to="/operator/dashboard">Back to Dashboard</Link>

      <h1>Loans &amp; Returns</h1>
      <p>View loans and manage book returns.</p>

      {message && <p role="status">{message}</p>}
      {error && <p role="alert">{error}</p>}
      {loadError && <p role="alert">{loadError}</p>}
      {loading && <p role="status">Loading loans...</p>}

      {!loading && !loadError && (
        <>
          <section>
            <h2>Active Loans</h2>

            {activeLoans.length === 0 ? (
              <p>No active loans.</p>
            ) : (
              <div className="loans-table-wrapper">
                <table className="loans-table">
                  <thead>
                    <tr>
                      <th>Book</th>
                      <th>Student ID</th>
                      <th>Borrowed</th>
                      <th>Due Date</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    {activeLoans.map((loan) => (
                      <tr key={loan.id}>
                        <td>{loan.book_title}</td>
                        <td>{loan.user_id}</td>
                        <td>{formatDate(loan.borrowed_at)}</td>
                        <td>{formatDate(loan.due_at)}</td>
                        <td>
                          {loan.is_overdue
                            ? `Overdue (${loan.days_overdue} days)`
                            : loan.status}
                        </td>
                        <td>
                          <button
                            type="button"
                            onClick={() => handleReturn(loan)}
                            disabled={returningId !== null}
                          >
                            {returningId === loan.id
                              ? 'Returning...'
                              : 'Return'}
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </section>

          <section>
            <h2>Return History</h2>

            {returnedLoans.length === 0 ? (
              <p>No returned books yet.</p>
            ) : (
              <div className="loans-table-wrapper">
                <table className="loans-table">
                  <thead>
                    <tr>
                      <th>Book</th>
                      <th>Student ID</th>
                      <th>Borrowed</th>
                      <th>Returned</th>
                      <th>Status</th>
                      <th>Penalty</th>
                    </tr>
                  </thead>

                  <tbody>
                    {returnedLoans.map((loan) => (
                      <tr key={loan.id}>
                        <td>{loan.book_title}</td>
                        <td>{loan.user_id}</td>
                        <td>{formatDate(loan.borrowed_at)}</td>
                        <td>{formatDate(loan.returned_at)}</td>
                        <td>{loan.status}</td>
                        <td>
                          {loan.penalties.length === 0
                            ? '-'
                            : loan.penalties.map((penalty) => (
                                <div key={penalty.id}>
                                  {penalty.amount} —{' '}
                                  {penalty.paid ? 'Paid' : 'Unpaid'}
                                </div>
                              ))}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </section>
        </>
      )}
    </main>
  )
}

export default OperatorLoansPage
