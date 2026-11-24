import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { apiRequest } from '../api/client'

const fields = [
  {
    key: 'borrow_fee',
    label: 'Borrow Fee (€)',
    step: '0.01',
    min: '0',
  },
  {
    key: 'penalty_rate',
    label: 'Penalty Rate (€/day)',
    step: '0.01',
    min: '0',
  },
  {
    key: 'borrowing_period',
    label: 'Borrowing Period (days)',
    step: '1',
    min: '1',
  },
  {
    key: 'borrowing_limit',
    label: 'Borrowing Limit (books)',
    step: '1',
    min: '1',
  },
]

function SuperAdminSettingsPage() {
  const [settings, setSettings] = useState(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')

  useEffect(() => {
    let ignore = false

    async function loadSettings() {
      try {
        const data = await apiRequest('/settings')

        if (
          !data.settings ||
          fields.some(({ key }) => data.settings[key] == null)
        ) {
          throw new Error('Invalid settings response')
        }

        if (!ignore) setSettings(data.settings)
      } catch (err) {
        if (!ignore) {
          setError(err.message || 'Failed to load settings')
        }
      } finally {
        if (!ignore) setLoading(false)
      }
    }

    loadSettings()

    return () => {
      ignore = true
    }
  }, [])

  function handleChange(event) {
    const { name, value } = event.target

    setSettings((current) => ({
      ...current,
      [name]: value,
    }))

    setError('')
    setMessage('')
  }

  async function handleSave(event) {
    event.preventDefault()
    if (saving || !settings) return

    setSaving(true)
    setError('')
    setMessage('')

    try {
      const data = await apiRequest('/settings', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(settings),
      })

      if (!data.settings) {
        throw new Error('Invalid settings response')
      }

      setSettings(data.settings)
      setMessage('Settings updated successfully.')
    } catch (err) {
      setError(err.message || 'Failed to update settings')
    } finally {
      setSaving(false)
    }
  }

  return (
    <main className="manage-books-page">
      <Link to="/super-admin/dashboard">
        Back to Dashboard
      </Link>

      <h1>System Settings</h1>
      <p>Manage borrowing fees, penalties and borrowing rules.</p>

      {loading && <p role="status">Loading settings...</p>}
      {error && <p role="alert">{error}</p>}
      {message && <p role="status">{message}</p>}

      {!loading && settings && (
        <form className="add-book-form" onSubmit={handleSave}>
          {fields.map((field) => (
            <div key={field.key}>
              <label htmlFor={field.key}>
                {field.label}
              </label>

              <input
                id={field.key}
                name={field.key}
                type="number"
                min={field.min}
                step={field.step}
                value={settings[field.key]}
                onChange={handleChange}
                disabled={saving}
                required
              />
            </div>
          ))}

          <div className="book-form-actions">
            <button type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </form>
      )}
    </main>
  )
}

export default SuperAdminSettingsPage
