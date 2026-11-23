import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

function BooksPage() {
  const [books, setBooks] = useState([])
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

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
