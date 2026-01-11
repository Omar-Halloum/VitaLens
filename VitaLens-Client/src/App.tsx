import { Routes, Route } from 'react-router-dom'
import { LandingPage } from './pages/LandingPage'
import './App.css'

function App() {
  return (
    <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="/login" element={<div>Login Page - Coming Soon</div>} />
      <Route path="/register" element={<div>Register Page - Coming Soon</div>} />
    </Routes>
  )
}

export default App