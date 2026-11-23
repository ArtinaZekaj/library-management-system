import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function MyLoansPage() {
  const [loans, setLoans] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

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
          setError(err.message || 'Failed to load loans')
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

  const activeLoans = loans.filter(
    (loan) => loan.returned_at === null
  )

  const loanHistory = loans.filter(
    (loan) => loan.returned_at !== null
  )

  function formatDate(value) {
    return value ? new Date(value).toLocaleDateString() : '-'
  }

  function renderLoans(items, isHistory) {
    if (items.length === 0) {
      return <p>No loans found.</p>
    }

    return (
      <div className="loans-table-wrapper">
        <table className="loans-table">
          <thead>
            <tr>
              <th>Book</th>
              <th>Borrowed</th>
              <th>Due Date</th>
              {isHistory && <th>Returned</th>}
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((loan) => (
              <tr key={loan.id}>
                <td>{loan.book_title}</td>
                <td>{formatDate(loan.borrowed_at)}</td>
                <td>{formatDate(loan.due_at)}</td>
                {isHistory && (
                  <td>{formatDate(loan.returned_at)}</td>
                )}
                <td>
                  {loan.status}
                  {loan.is_overdue && (
                    <span> ({loan.days_overdue} days late)</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    )
  }

  return (
    <main className="loans-page">
      <Link to="/dashboard">Back to Dashboard</Link>

      <h1>My Loans</h1>
      <p>View your current and previous book loans.</p>

      {loading && <p role="status">Loading loans...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        <>
          <section>
            <h2>Active Loans</h2>
            {renderLoans(activeLoans, false)}
          </section>

          <section>
            <h2>Loan History</h2>
            {renderLoans(loanHistory, true)}
          </section>
        </>
      )}
    </main>
  )
}

export default MyLoansPage
