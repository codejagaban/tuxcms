import { lazy, Suspense } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './lib/auth';
import DashboardLayout from './layouts/DashboardLayout';
import Login from './pages/Login';
import Spinner from './components/ui/Spinner';

const Dashboard = lazy(() => import('./pages/Dashboard'));
const Pages = lazy(() => import('./pages/placeholders/Pages'));
const PageEditor = lazy(() => import('./pages/PageEditor'));
const Media = lazy(() => import('./pages/placeholders/Media'));
const Settings = lazy(() => import('./pages/placeholders/Settings'));
const SEO = lazy(() => import('./pages/placeholders/SEO'));
const Jobs = lazy(() => import('./pages/Jobs'));

const Deferred = ({ children }) => (
  <Suspense fallback={<div className="grid min-h-72 place-items-center"><Spinner size="lg" /></div>}>
    {children}
  </Suspense>
);

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
          element={<ProtectedRoute><Deferred><PageEditor /></Deferred></ProtectedRoute>}
        />
        <Route
          path="/dashboard/pages/:id/edit"
          element={<ProtectedRoute><Deferred><PageEditor /></Deferred></ProtectedRoute>}
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
          <Route index element={<Deferred><Dashboard /></Deferred>} />
          <Route path="pages" element={<Deferred><Pages /></Deferred>} />
          <Route path="jobs" element={<Deferred><Jobs /></Deferred>} />
          <Route path="media" element={<Deferred><Media /></Deferred>} />
          <Route path="settings" element={<Deferred><Settings /></Deferred>} />
          <Route path="seo" element={<Deferred><SEO /></Deferred>} />
        </Route>

        {/* Catch all - redirect to dashboard */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </>
  );
}

export default App;
