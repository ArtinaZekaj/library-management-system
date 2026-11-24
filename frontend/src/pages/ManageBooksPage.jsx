import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'
import { useAuth } from '../context/auth-context'

const emptyForm = {
    title: '',
    author: '',
    isbn: '',
    category: '',
    stock: '0',
}

function ManageBooksPage() {
    const { user } = useAuth()

    const dashboardPath =
        user?.role === 'super_admin'
            ? '/super-admin/dashboard'
            : '/operator/dashboard'

    const [books, setBooks] = useState([]) 
    const [search, setSearch] = useState('')
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [message, setMessage] = useState('')
    const [showForm, setShowForm] = useState(false)
    const [saving, setSaving] = useState(false)
    const [form, setForm] = useState(emptyForm)
    const [editingBookId, setEditingBookId] = useState(null)
    const [loadError, setLoadError] = useState('')
    const [deletingBookId, setDeletingBookId] = useState(null)

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
                    setLoadError(err.message || 'Failed to load books')
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

    function handleChange(event) {
        const { name, value } = event.target
        setForm((current) => ({ ...current, [name]: value }))
    }

    function handleEdit(book) {
        setEditingBookId(book.id)
        setForm({
            title: book.title,
            author: book.author,
            isbn: book.isbn,
            category: book.category,
            stock: String(book.stock),
        })
        setShowForm(true)
        setError('')
        setMessage('')
    }

    function handleCancel() {
        setShowForm(false)
        setEditingBookId(null)
        setForm(emptyForm)
        setError('')
        setMessage('')
    }

    async function handleDelete(book) {
        if (deletingBookId !== null || saving) return

        const confirmed = window.confirm(
            `Are you sure you want to delete "${book.title}"?`
        )

        if (!confirmed) return

        setError('')
        setMessage('')
        setDeletingBookId(book.id)

        try {
            await apiRequest(`/books/${book.id}`, {
                method: 'DELETE',
            })

            setBooks((current) =>
                current.filter((item) => item.id !== book.id)
            )

            if (editingBookId === book.id) {
                handleCancel()
            }

            setMessage('Book deleted successfully.')
        } catch (err) {
            setError(err.message || 'Failed to delete book')
        } finally {
            setDeletingBookId(null)
        }
    }

    async function handleSaveBook(event) {
        event.preventDefault()
        if (saving) return

        setError('')
        setMessage('')
        setSaving(true)

        const isEditing = editingBookId !== null

        try {
            const data = await apiRequest(
                isEditing ? `/books/${editingBookId}` : '/books',
                {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: form.title.trim(),
                        author: form.author.trim(),
                        isbn: form.isbn.trim(),
                        category: form.category.trim(),
                        stock: Number(form.stock),
                    }),
                }
            )

            if (!data.book || data.book.id == null) {
                throw new Error('Invalid book response')
            }

            if (isEditing) {
                setBooks((current) =>
                    current.map((book) =>
                        book.id === data.book.id ? data.book : book
                    )
                )
            } else {
                setBooks((current) => [...current, data.book])
            }

            setForm(emptyForm)
            setEditingBookId(null)
            setShowForm(false)
            setMessage(
                isEditing
                    ? 'Book updated successfully.'
                    : 'Book added successfully.'
            )
        } catch (err) {
            setError(
                err.message ||
                (isEditing ? 'Failed to update book' : 'Failed to add book')
            )
        } finally {
            setSaving(false)
        }
    }

    const query = search.trim().toLowerCase()

    const filteredBooks = books.filter(
        (book) =>
            book.title.toLowerCase().includes(query) ||
            book.author.toLowerCase().includes(query)
    )

    return (
        <main className="manage-books-page">
            <Link to={dashboardPath}>Back to Dashboard</Link>

            <h1>Manage Books</h1>
            <p>View and manage library books.</p>

            {!showForm && (
                <button
                    type="button"
                    onClick={() => {
                        setEditingBookId(null)
                        setForm(emptyForm)
                        setShowForm(true)
                        setError('')
                        setMessage('')
                    }}
                >
                    Add Book
                </button>
            )}

            {showForm && (
                <form className="add-book-form" onSubmit={handleSaveBook}>
                    <h2>{editingBookId !== null ? 'Edit Book' : 'Add Book'}</h2>

                    {['title', 'author', 'isbn', 'category'].map((field) => (
                        <div key={field}>
                            <label htmlFor={`book-${field}`}>
                                {field === 'isbn'
                                    ? 'ISBN'
                                    : field.charAt(0).toUpperCase() + field.slice(1)}
                            </label>
                            <input
                                id={`book-${field}`}
                                name={field}
                                value={form[field]}
                                onChange={handleChange}
                                maxLength={255}
                                required
                            />
                        </div>
                    ))}

                    <div>
                        <label htmlFor="book-stock">Stock</label>
                        <input
                            id="book-stock"
                            name="stock"
                            type="number"
                            min="0"
                            max="2147483647"
                            step="1"
                            value={form.stock}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div className="book-form-actions">
                        <button type="submit" disabled={saving}>
                            {saving
                                ? 'Saving...'
                                : editingBookId !== null
                                    ? 'Save Changes'
                                    : 'Save Book'}
                        </button>

                        <button
                            type="button"
                            onClick={handleCancel}
                            disabled={saving}
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            )}

            {message && <p role="status">{message}</p>}
            {error && <p role="alert">{error}</p>}
            {loadError && <p role="alert">{loadError}</p>}

            <div className="manage-books-search">
                <label htmlFor="manage-book-search">Search books</label>
                <input
                    id="manage-book-search"
                    type="search"
                    placeholder="Search by title or author"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
            </div>

            {loading && <p role="status">Loading books...</p>}

            {!loading && !loadError && (
                filteredBooks.length === 0 ? (
                    <p>
                        {books.length === 0
                            ? 'No books available.'
                            : 'No books found.'}
                    </p>
                ) : (
                    <div className="manage-books-table-wrapper">
                        <table className="manage-books-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredBooks.map((book) => (
                                    <tr key={book.id}>
                                        <td>{book.title}</td>
                                        <td>{book.author}</td>
                                        <td>{book.isbn}</td>
                                        <td>{book.category}</td>
                                        <td>{book.stock}</td>
                                        <td>{book.status}</td>
                                        <td>

                                            <td>
                                                <button
                                                    type="button"
                                                    onClick={() => handleEdit(book)}
                                                    disabled={saving || deletingBookId !== null}
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(book)}
                                                    disabled={saving || deletingBookId !== null}
                                                >
                                                    {deletingBookId === book.id ? 'Deleting...' : 'Delete'}
                                                </button>
                                            </td>
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

export default ManageBooksPage
