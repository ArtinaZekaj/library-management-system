import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'
import { useAuth } from '../context/auth-context'

function BooksPage() {
  const { user } = useAuth()
  const isStudent = user?.role === 'student'
  const [books, setBooks] = useState([])
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [selectedBook, setSelectedBook] = useState(null)
  const [borrowFee, setBorrowFee] = useState(null)
  const [feeConfirmed, setFeeConfirmed] = useState(false)
  const [loadingFeeId, setLoadingFeeId] = useState(null)
  const [borrowing, setBorrowing] = useState(false)

  useEffect(() => {
    let ignore = false

    async function loadBooks() {
      try {
        const data = await apiRequest('/books')

        if (!ignore) {
          if (!Array.isArray(data.books)) {
            throw new Error('Invalid books response')
          }

          setBooks(data.books)
        }
      } catch (err) {
        if (!ignore) {
          setError(err.message || 'Failed to load books')
        }
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    loadBooks()

    return () => {
      ignore = true
    }
  }, [])

  async function handleSelectBook(book) {
    if (!isStudent || book.stock <= 0 || loadingFeeId !== null || borrowing) return

    setError('')
    setMessage('')
    setSelectedBook(null)
    setFeeConfirmed(false)
    setLoadingFeeId(book.id)

    try {
      const data = await apiRequest('/borrowing-fee')

      if (
        (typeof data.borrow_fee !== 'string' &&
          typeof data.borrow_fee !== 'number') ||
        !/^\d+(\.\d{1,2})?$/.test(String(data.borrow_fee))
      ) {
        throw new Error('Invalid borrowing fee response')
      }

      setBorrowFee(Number(data.borrow_fee).toFixed(2))
      setSelectedBook(book)
    } catch (err) {
      setError(err.message || 'Failed to load borrowing fee')
    } finally {
      setLoadingFeeId(null)
    }
  }

  function handleCancel() {
    setSelectedBook(null)
    setBorrowFee(null)
    setFeeConfirmed(false)
  }

  async function handleBorrow() {
    if (!selectedBook || !feeConfirmed || borrowing) return

    setError('')
    setMessage('')
    setBorrowing(true)

    try {
      const data = await apiRequest('/loans', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          book_id: selectedBook.id,
          fee_confirmed: true,
        }),
      })

      const remainingStock = data.loan?.remaining_stock

      if (!Number.isInteger(remainingStock) || remainingStock < 0) {
        throw new Error('Check My Loans to verify the borrowing result')
      }

      setBooks((current) =>
        current.map((book) =>
          book.id === selectedBook.id
            ? {
              ...book,
              stock: remainingStock,
              status: remainingStock === 0 ? 'Unavailable' : book.status,
            }
            : book
        )
      )

      setSelectedBook(null)
      setBorrowFee(null)
      setFeeConfirmed(false)
      setMessage('Book borrowed successfully.')
    } catch (err) {
      setError(
        `${err.message || 'Borrowing failed.'} Check My Loans before retrying.`
      )
    } finally {
      setBorrowing(false)
    }
  }

  const filteredBooks = books.filter((book) => {
    const query = search.trim().toLowerCase()

    return (
      book.title.toLowerCase().includes(query) ||
      book.author.toLowerCase().includes(query)
    )
  })

  return (
    <main className="books-page">
      <Link to="/dashboard">Back to Dashboard</Link>

      <h1>Books</h1>
      <p>Browse and search library books.</p>

      {message && <p role="status">{message}</p>}

      {selectedBook && (
        <section aria-label="Borrow confirmation">
          <h2>Borrow Book</h2>
          <p>Book: {selectedBook.title}</p>
          <p>Borrowing fee: €{borrowFee}</p>

          <label htmlFor="fee-confirmation">
            <input
              id="fee-confirmation"
              type="checkbox"
              checked={feeConfirmed}
              disabled={borrowing}
              onChange={(e) => setFeeConfirmed(e.target.checked)}
            />
            I confirm the borrowing fee.
          </label>

          <div>
            <button
              type="button"
              onClick={handleBorrow}
              disabled={!feeConfirmed || borrowing}
            >
              {borrowing ? 'Borrowing...' : 'Confirm Borrow'}
            </button>

            <button
              type="button"
              onClick={handleCancel}
              disabled={borrowing}
            >
              Cancel
            </button>
          </div>
        </section>
      )}

      <label htmlFor="book-search">Search books</label>
      <input
        id="book-search"
        type="search"
        placeholder="Search by title or author"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
      />

      {loading && <p role="status">Loading books...</p>}
      {error && <p role="alert">{error}</p>}

      {!loading && !error && (
        filteredBooks.length === 0 ? (
          <p>
            {books.length === 0
              ? 'No books available.'
              : 'No books found.'}
          </p>
        ) : (
          <div className="books-table-wrapper">
            <table className="books-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Author</th>
                  <th>Category</th>
                  <th>ISBN</th>
                  <th>Stock</th>
                  <th>Status</th>
                  {isStudent && <th>Action</th>}
                </tr>
              </thead>
              <tbody>
                {filteredBooks.map((book) => (
                  <tr key={book.id}>
                    <td>{book.title}</td>
                    <td>{book.author}</td>
                    <td>{book.category}</td>
                    <td>{book.isbn}</td>
                    <td>{book.stock}</td>
                    <td>{book.status}</td>
                    {isStudent && (
                      <td>
                        <button
                          type="button"
                          onClick={() => handleSelectBook(book)}
                          disabled={
                            book.stock <= 0 ||
                            loadingFeeId !== null ||
                            borrowing
                          }
                        >
                          {loadingFeeId === book.id
                            ? 'Loading...'
                            : book.stock <= 0
                              ? 'Unavailable'
                              : 'Borrow'}
                        </button>
                      </td>
                    )}
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

export default BooksPage
