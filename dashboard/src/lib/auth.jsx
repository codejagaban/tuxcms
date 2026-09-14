import { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { authAPI } from './api';
import toast from 'react-hot-toast';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Restore only sessions the API still accepts. A token in localStorage is
  // not proof that it has not expired or been revoked.
  useEffect(() => {
    let active = true;

    const restoreSession = async () => {
      try {
      const storedToken = localStorage.getItem('auth_token');

        if (storedToken) {
          const response = await authAPI.getMe();
          const currentUser = response.data.data;

          if (active) {
            setToken(storedToken);
            setUser(currentUser);
            setIsAuthenticated(true);
            localStorage.setItem('user', JSON.stringify(currentUser));
          }
        }
      } catch (error) {
        console.error('Error restoring session:', error);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      } finally {
        if (active) setIsLoading(false);
      }
    };

    restoreSession();

    return () => { active = false; };
  }, []);

  const login = async (email, password) => {
    try {
      const response = await authAPI.login(email, password);
      const { token: newToken, data: userData } = response.data;

      setToken(newToken);
      setUser(userData);
      setIsAuthenticated(true);

      localStorage.setItem('auth_token', newToken);
      localStorage.setItem('user', JSON.stringify(userData));

      return response.data;
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed';
      toast.error(message);
      throw error;
    }
  };

  const logout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      console.error('Logout API error:', error);
    } finally {
      setUser(null);
      setToken(null);
      setIsAuthenticated(false);

      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');

      navigate('/login');
      toast.success('Logged out successfully');
    }
  };

  const value = { user, token, isLoading, isAuthenticated, login, logout };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

export const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, isLoading } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      navigate('/login');
    }
  }, [isAuthenticated, isLoading, navigate]);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-screen bg-gray-50">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div>
          <p className="mt-4 text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  return isAuthenticated ? children : null;
};
