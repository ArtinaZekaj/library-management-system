import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

const emptyForm = {
    name: '',
    email: '',
    password: '',
    role: 'student',
}

function UserManagementPage() {
    const [users, setUsers] = useState([])
    const [loading, setLoading] = useState(true)
    const [loadError, setLoadError] = useState('')
    const [error, setError] = useState('')
    const [message, setMessage] = useState('')
    const [showForm, setShowForm] = useState(false)
    const [editingId, setEditingId] = useState(null)
    const [form, setForm] = useState(emptyForm)
    const [saving, setSaving] = useState(false)
    const [deletingId, setDeletingId] = useState(null)

    useEffect(() => {
        let ignore = false

        async function loadUsers() {
            try {
                const data = await apiRequest('/users')

                if (!Array.isArray(data.users)) {
                    throw new Error('Invalid users response')
                }

                if (!ignore) setUsers(data.users)
            } catch (err) {
                if (!ignore) {
                    setLoadError(err.message || 'Failed to load users')
                }
            } finally {
                if (!ignore) setLoading(false)
            }
        }

        loadUsers()

        return () => {
            ignore = true
        }
    }, [])

    function handleChange(event) {
        const { name, value } = event.target
        setForm((current) => ({ ...current, [name]: value }))
    }

    function handleAdd() {
        setEditingId(null)
        setForm(emptyForm)
        setError('')
        setMessage('')
        setShowForm(true)
    }

    function handleEdit(user) {
        setEditingId(user.id)
        setForm({
            name: user.name,
            email: user.email,
            password: '',
            role: user.role,
        })
        setError('')
        setMessage('')
        setShowForm(true)
    }

    function handleCancel() {
        setShowForm(false)
        setEditingId(null)
        setForm(emptyForm)
        setError('')
    }

    async function handleSave(event) {
        event.preventDefault()

        if (saving || deletingId !== null) return

        setSaving(true)
        setError('')
        setMessage('')

        const isEditing = editingId !== null

        const payload = {
            name: form.name.trim(),
            email: form.email.trim(),
            role: form.role,
        }

        if (!isEditing || form.password !== '') {
            payload.password = form.password
        }

        try {
            await apiRequest(
                isEditing ? `/users/${editingId}` : '/users',
                {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                }
            )

            const data = await apiRequest('/users')

            if (!Array.isArray(data.users)) {
                throw new Error('Invalid users response')
            }

            setUsers(data.users)
            setShowForm(false)
            setEditingId(null)
            setForm(emptyForm)
            setMessage(
                isEditing
                    ? 'User updated successfully.'
                    : 'User created successfully.'
            )
        } catch (err) {
            setError(err.message || 'Failed to save user')
        } finally {
            setSaving(false)
        }
    }

    async function handleDelete(user) {
        if (deletingId !== null || saving) return

        const confirmed = window.confirm(
            `Are you sure you want to delete "${user.name}"?`
        )

        if (!confirmed) return

        setError('')
        setMessage('')
        setDeletingId(user.id)

        try {
            await apiRequest(`/users/${user.id}`, {
                method: 'DELETE',
            })

            setUsers((current) =>
                current.filter((item) => item.id !== user.id)
            )

            if (editingId === user.id) {
                handleCancel()
            }

            setMessage('User deleted successfully.')
        } catch (err) {
            setError(err.message || 'Failed to delete user')
        } finally {
            setDeletingId(null)
        }
    }

    return (
        <main className="manage-books-page">
            <Link to="/super-admin/dashboard">
                Back to Dashboard
            </Link>

            <h1>User Management</h1>
            <p>Manage student and operator accounts.</p>

            {!showForm && (
                <button
                    type="button"
                    onClick={handleAdd}
                    disabled={saving || deletingId !== null}
                >
                    Add User
                </button>
            )}

            {showForm && (
                <form className="add-book-form" onSubmit={handleSave}>
                    <h2>
                        {editingId !== null ? 'Edit User' : 'Add User'}
                    </h2>
                    {error && error !== 'Email already exists' && (
                        <p role="alert">{error}</p>
                    )}

                    <div>
                        <label htmlFor="user-name">Name</label>
                        <input
                            id="user-name"
                            name="name"
                            value={form.name}
                            onChange={handleChange}
                            maxLength={255}
                            required
                        />
                    </div>

                    <div>
                        <label htmlFor="user-email">Email</label>
                        <input
                            id="user-email"
                            name="email"
                            type="email"
                            value={form.email}
                            onChange={handleChange}
                            maxLength={255}
                            aria-invalid={error === 'Email already exists'}
                            aria-describedby={
                                error === 'Email already exists' ? 'email-error' : undefined
                            }
                            required
                        />

                        {error === 'Email already exists' && (
                            <p id="email-error" role="alert" style={{ color: '#dc2626' }}>
                                {error}
                            </p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="user-role">Role</label>
                        <select
                            id="user-role"
                            name="role"
                            value={form.role}
                            onChange={handleChange}
                            required
                        >
                            <option value="student">Student</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>

                    <div>
                        <label htmlFor="user-password">
                            {editingId !== null
                                ? 'New Password (leave blank to keep current)'
                                : 'Password'}
                        </label>
                        <input
                            id="user-password"
                            name="password"
                            type="password"
                            value={form.password}
                            onChange={handleChange}
                            autoComplete="new-password"
                            required={editingId === null}
                        />
                    </div>

                    <div className="book-form-actions">
                        <button
                            type="submit"
                            disabled={saving || deletingId !== null}
                        >
                            {saving ? 'Saving...' : 'Save User'}
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
            {!showForm && error && <p role="alert">{error}</p>}
            {loadError && <p role="alert">{loadError}</p>}
            {loading && <p role="status">Loading users...</p>}

            {!loading && !loadError && (
                users.length === 0 ? (
                    <p>No users found.</p>
                ) : (
                    <div className="manage-books-table-wrapper">
                        <table className="manage-books-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.map((user) => (
                                    <tr key={user.id}>
                                        <td>{user.name}</td>
                                        <td>{user.email}</td>
                                        <td>{user.role}</td>
                                        <td>
                                            <button
                                                type="button"
                                                onClick={() => handleEdit(user)}
                                                disabled={saving || deletingId !== null}
                                            >
                                                Edit
                                            </button>{' '}
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(user)}
                                                disabled={saving || deletingId !== null}
                                            >
                                                {deletingId === user.id
                                                    ? 'Deleting...'
                                                    : 'Delete'}
                                            </button>
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

export default UserManagementPage
