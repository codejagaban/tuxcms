import axios from 'axios';

const API_BASE_URL = '/api/v1';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor - attach token and tenant ID
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    const tenantId = localStorage.getItem('current_tenant_id');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    if (tenantId) {
      config.headers['X-Tenant-Id'] = tenantId;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - handle 401 errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      localStorage.removeItem('current_tenant_id');
      localStorage.removeItem('tenants');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth endpoints
export const authAPI = {
  login: (email, password) =>
    api.post('/auth/login', { email, password }),

  register: (name, email, password, password_confirmation) =>
    api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation,
    }),

  logout: () =>
    api.post('/auth/logout'),

  getMe: () =>
    api.get('/auth/me'),

  getMyTenants: () =>
    api.get('/auth/tenants'),
};

// Tenant endpoints
export const tenantAPI = {
  switchTenant: (tenantId) => {
    localStorage.setItem('current_tenant_id', tenantId);
    // Return a promise for consistency
    return Promise.resolve();
  },

  list: (params = {}) =>
    api.get('/admin/tenants', { params }),

  get: (tenantId) =>
    api.get(`/admin/tenants/${tenantId}`),

  create: (data) =>
    api.post('/admin/tenants', data),

  update: (tenantId, data) =>
    api.put(`/admin/tenants/${tenantId}`, data),

  delete: (tenantId) =>
    api.delete(`/admin/tenants/${tenantId}`),

  // Non-admin tenant routes
  mine: () =>
    api.get('/tenants/mine'),

  current: () =>
    api.get('/tenants/current'),
};

// Page endpoints
export const pageAPI = {
  list: (params = {}) =>
    api.get('/pages', { params }),

  get: (pageId) =>
    api.get(`/pages/${pageId}`),

  create: (data) =>
    api.post('/pages', data),

  update: (pageId, data) =>
    api.put(`/pages/${pageId}`, data),

  delete: (pageId) =>
    api.delete(`/pages/${pageId}`),
};

// Form endpoints
export const formAPI = {
  list: (params = {}) =>
    api.get('/forms', { params }),

  get: (formId) =>
    api.get(`/forms/${formId}`),

  create: (data) =>
    api.post('/forms', data),

  update: (formId, data) =>
    api.put(`/forms/${formId}`, data),

  delete: (formId) =>
    api.delete(`/forms/${formId}`),

  getSubmissions: (formId, params = {}) =>
    api.get(`/forms/${formId}/submissions`, { params }),

  deleteSubmission: (submissionId) =>
    api.delete(`/submissions/${submissionId}`),
};

// Media endpoints
export const mediaAPI = {
  list: (params = {}) =>
    api.get('/media', { params }),

  upload: (file) => {
    const formData = new FormData();
    formData.append('file', file);
    return api.post('/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  delete: (mediaId) =>
    api.delete(`/media/${mediaId}`),
};

// Menu endpoints
export const menuAPI = {
  list: (params = {}) =>
    api.get('/menus', { params }),

  get: (menuId) =>
    api.get(`/menus/${menuId}`),

  create: (data) =>
    api.post('/menus', data),

  update: (menuId, data) =>
    api.put(`/menus/${menuId}`, data),

  delete: (menuId) =>
    api.delete(`/menus/${menuId}`),
};

// Settings endpoints
export const settingsAPI = {
  get: () =>
    api.get('/settings'),

  update: (data) =>
    api.put('/settings', data),
};

// SEO endpoints
export const seoAPI = {
  get: (pageId) =>
    api.get(`/pages/${pageId}/seo`),

  update: (pageId, data) =>
    api.put(`/pages/${pageId}/seo`, data),
};

export default api;
