import { Routes, Route, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './lib/auth';
import DashboardLayout from './layouts/DashboardLayout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Pages from './pages/placeholders/Pages';
import PageEditor from './pages/PageEditor';
import Media from './pages/placeholders/Media';
import Settings from './pages/placeholders/Settings';
import SEO from './pages/placeholders/SEO';

function App() {
  return (
    <>
      <Routes>
        {/* Public Routes — there is no public registration */}
        <Route path="/login" element={<Login />} />

        {/* Redirect root to dashboard */}
        <Route path="/" element={<Navigate to="/dashboard" replace />} />

        {/* Full-screen visual page editor (outside the dashboard chrome) */}
        <Route
          path="/dashboard/pages/create"
          element={<ProtectedRoute><PageEditor /></ProtectedRoute>}
        />
        <Route
          path="/dashboard/pages/:id/edit"
          element={<ProtectedRoute><PageEditor /></ProtectedRoute>}
        />

        {/* Protected Dashboard Routes */}
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <DashboardLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<Dashboard />} />
          <Route path="pages" element={<Pages />} />
          <Route path="media" element={<Media />} />
          <Route path="settings" element={<Settings />} />
          <Route path="seo" element={<SEO />} />
        </Route>

        {/* Catch all - redirect to dashboard */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </>
  );
}

export default App;
