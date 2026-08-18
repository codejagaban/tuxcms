import { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { authAPI, tenantAPI } from './api';
import toast from 'react-hot-toast';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [tenants, setTenants] = useState([]);
  const [currentTenant, setCurrentTenant] = useState(null);
  const [isSuperAdmin, setIsSuperAdmin] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Load user from localStorage on mount
  useEffect(() => {
    const loadUser = async () => {
      try {
        const storedToken = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');
        const storedTenants = localStorage.getItem('tenants');
        const storedCurrentTenant = localStorage.getItem('current_tenant_id');
        const storedIsSuperAdmin = localStorage.getItem('is_super_admin');

        if (storedToken && storedUser) {
          setToken(storedToken);
          const userData = JSON.parse(storedUser);
          setUser(userData);
          setIsAuthenticated(true);
          setIsSuperAdmin(storedIsSuperAdmin === 'true' || userData.is_super_admin || false);

          if (storedTenants) {
            const tenantsData = JSON.parse(storedTenants);
            setTenants(tenantsData);

            if (storedCurrentTenant) {
              const tenant = tenantsData.find(
                (t) => String(t.id) === String(storedCurrentTenant)
              );
              if (tenant) {
                setCurrentTenant(tenant);
              } else if (tenantsData.length > 0) {
                // Fallback to first tenant
                setCurrentTenant(tenantsData[0]);
                localStorage.setItem('current_tenant_id', String(tenantsData[0].id));
              }
            } else if (tenantsData.length > 0) {
              // No stored tenant but tenants exist — auto-select first
              setCurrentTenant(tenantsData[0]);
              localStorage.setItem('current_tenant_id', String(tenantsData[0].id));
            }
          }
        }
      } catch (error) {
        console.error('Error loading user:', error);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('tenants');
        localStorage.removeItem('current_tenant_id');
        localStorage.removeItem('is_super_admin');
      } finally {
        setIsLoading(false);
      }
    };

    loadUser();
  }, []);

  const login = async (email, password) => {
    try {
      const response = await authAPI.login(email, password);
      const { token: newToken, data: userData, tenants: userTenants, is_super_admin: superAdmin } = response.data;

      setToken(newToken);
      setUser(userData);
      setIsAuthenticated(true);
      setIsSuperAdmin(superAdmin || false);

      localStorage.setItem('auth_token', newToken);
      localStorage.setItem('user', JSON.stringify(userData));
      localStorage.setItem('is_super_admin', String(superAdmin || false));

      if (userTenants && userTenants.length > 0) {
        setTenants(userTenants);
        localStorage.setItem('tenants', JSON.stringify(userTenants));

        // Auto-select first tenant
        const firstTenant = userTenants[0];
        setCurrentTenant(firstTenant);
        localStorage.setItem('current_tenant_id', String(firstTenant.id));
      }

      return response.data;
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed';
      toast.error(message);
      throw error;
    }
  };

  const register = async (name, email, password, password_confirmation) => {
    try {
      const response = await authAPI.register(
        name,
        email,
        password,
        password_confirmation
      );
      toast.success('Registration successful! Please log in.');
      return response.data;
    } catch (error) {
      const message = error.response?.data?.message || 'Registration failed';
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
      setTenants([]);
      setCurrentTenant(null);
      setIsAuthenticated(false);
      setIsSuperAdmin(false);

      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      localStorage.removeItem('tenants');
      localStorage.removeItem('current_tenant_id');
      localStorage.removeItem('is_super_admin');

      navigate('/login');
      toast.success('Logged out successfully');
    }
  };

  const switchTenant = async (tenantId) => {
    try {
      const tenant = tenants.find((t) => t.id === tenantId);
      if (tenant) {
        await tenantAPI.switchTenant(tenantId);
        setCurrentTenant(tenant);
        localStorage.setItem('current_tenant_id', String(tenantId));
        toast.success(`Switched to ${tenant.name}`);
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to switch tenant';
      toast.error(message);
      throw error;
    }
  };

  const value = {
    user,
    token,
    tenants,
    currentTenant,
    isSuperAdmin,
    isLoading,
    isAuthenticated,
    login,
    register,
    logout,
    switchTenant,
  };

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
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  return isAuthenticated ? children : null;
};
