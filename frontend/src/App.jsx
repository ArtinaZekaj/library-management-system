import { Routes, Route, Navigate } from 'react-router'
import './App.css'

function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<h1>Login Page</h1>} />
      <Route path="/dashboard" element={<h1>Dashboard Page</h1>} />
      <Route path="*" element={<h1>Page Not Found</h1>} />
    </Routes>
  )
}

export default App
