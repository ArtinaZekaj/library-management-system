import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function PenaltiesPage() {
  const [penalties, setPenalties] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [payingId, setPayingId] = useState(null)

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

  async function handlePay(penalty) {
    if (payingId !== null || penalty.paid) return

    const confirmed = window.confirm(
      `Confirm payment of €${penalty.amount}?`
    )

    if (!confirmed) return

    setError('')
    setMessage('')
    setPayingId(penalty.id)

    try {
      const data = await apiRequest(`/penalties/${penalty.id}/pay`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          payment_confirmed: true,
        }),
      })

      if (
        data.penalty?.paid !== true ||
        Number(data.penalty.penalty_id) !== Number(penalty.id)
      ) {
        throw new Error('Refresh the page to verify the payment')
      }

      setPenalties((current) =>
        current.map((item) =>
          item.id === penalty.id
            ? { ...item, paid: true }
            : item
        )
      )

      setMessage('Penalty payment recorded successfully.')
    } catch (err) {
      setError(
        `${err.message || 'Payment failed.'} Refresh before retrying.`
      )
    } finally {
      setPayingId(null)
    }
  }

  return (
    <main className="penalties-page">
      <Link to="/dashboard">Back to Dashboard</Link>

      <h1>Penalties</h1>
      <p>View your library penalties.</p>

      {message && <p role="status">{message}</p>}

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
                  <th>Action</th>
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
                    <td>
                      {!penalty.paid && (
                        <button
                          type="button"
                          onClick={() => handlePay(penalty)}
                          disabled={payingId !== null}
                        >
                          {payingId === penalty.id
                            ? 'Processing...'
                            : 'Pay Penalty'}
                        </button>
                      )}
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
