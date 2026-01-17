import { Routes, Route, Navigate } from 'react-router-dom';
import { LandingPage } from '../pages/LandingPage';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';
import { DashboardPage } from '../pages/DashboardPage';
import { DocumentsPage } from '../pages/DocumentsPage';
import { DailyLogPage } from '../pages/DailyLogPage';
import { ChatPage } from '../pages/ChatPage';
import { AuthenticatedLayout } from '../components/Layout/AuthenticatedLayout';
import { RiskDetailsPage } from '../pages/RiskDetailsPage';
import { useAuth } from '../context/AuthContext';

export const AppRouter = () => {
  const { isAuthenticated } = useAuth();

  // if (isLoading) {
  //   return <div>Loading...</div>;
  // }

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
                <DocumentsPage />
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/logs" 
            element={
              <AuthenticatedLayout>
                <DailyLogPage />
              </AuthenticatedLayout>
            } 
          />
          <Route 
            path="/chat" 
            element={
              <AuthenticatedLayout>
                <ChatPage />
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
          <Route 
            path="/risks/:riskKey" 
            element={
              <AuthenticatedLayout>
                <RiskDetailsPage />
              </AuthenticatedLayout>
            } 
          />
          {/* Catch-all for authenticated users */}
          <Route path="*" element={<Navigate to="/dashboard" />} />
        </>
      )}

      {/* Catch-all redirect for protected routes if not authenticated */}
      {!isAuthenticated && (
        <Route path="*" element={<Navigate to="/login" />} />
      )}
    </Routes>
  );
};