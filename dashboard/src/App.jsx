import { Routes, Route, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './lib/auth';
import DashboardLayout from './layouts/DashboardLayout';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Pages from './pages/placeholders/Pages';
import PageEditor from './pages/PageEditor';
import Menus from './pages/placeholders/Menus';
import Forms from './pages/placeholders/Forms';
import FormSubmissions from './pages/placeholders/FormSubmissions';
import Media from './pages/placeholders/Media';
import Settings from './pages/placeholders/Settings';
import SEO from './pages/placeholders/SEO';
import Tenants from './pages/placeholders/Tenants';

function App() {
  return (
    <>
      <Routes>
        {/* Public Routes */}
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />

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
          <Route path="menus" element={<Menus />} />
          <Route path="forms" element={<Forms />} />
          <Route path="forms/:id/submissions" element={<FormSubmissions />} />
          <Route path="media" element={<Media />} />
          <Route path="settings" element={<Settings />} />
          <Route path="seo" element={<SEO />} />
          <Route path="tenants" element={<Tenants />} />
        </Route>

        {/* Catch all - redirect to dashboard */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </>
  );
}

export default App;
