import { Routes, Route, Navigate } from 'react-router-dom';
import { LandingPage } from '../pages/LandingPage';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';
import { DashboardPage } from '../pages/DashboardPage';
import { AuthenticatedLayout } from '../components/Layout/AuthenticatedLayout';
import { useAuth } from '../context/AuthContext';

export const AppRouter = () => {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <Routes>
      {/* Public Routes */}
      <Route path="/" element={<LandingPage />} />
      <Route 
        path="/login" 
        element={!isAuthenticated ? <LoginPage /> : <Navigate to="/dashboard" />} 
      />
      <Route 
        path="/register" 
        element={!isAuthenticated ? <RegisterPage /> : <Navigate to="/dashboard" />} 
      />

      {/* Protected Routes */}
      {isAuthenticated && (
        <>
          <Route 
            path="/dashboard" 
            element={
              <AuthenticatedLayout>
                <DashboardPage />
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/documents" 
            element={
              <AuthenticatedLayout>
                <div>Documents - Coming Soon</div>
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/logs" 
            element={
              <AuthenticatedLayout>
                <div>Daily Logs - Coming Soon</div>
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/chat" 
            element={
              <AuthenticatedLayout>
                <div>Ask AI - Coming Soon</div>
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/profile" 
            element={
              <AuthenticatedLayout>
                <div>Profile - Coming Soon</div>
              </AuthenticatedLayout>
            } 
          />
        </>
      )}

      {/* Catch-all redirect for protected routes if not authenticated */}
      {!isAuthenticated && (
        <Route path="*" element={<Navigate to="/login" />} />
      )}
    </Routes>
  );
};