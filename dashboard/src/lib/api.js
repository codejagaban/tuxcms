import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api/v1';
const DASHBOARD_BASE_URL = import.meta.env.BASE_URL.replace(/\/$/, '');

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export const apiErrorMessage = (error, fallback = 'Request failed') => {
  if (!error.response) {
    return 'Cannot reach the CMS server. Check that it is running and try again.';
  }

  const validationErrors = error.response.data?.errors;
  if (validationErrors) {
    const first = Object.values(validationErrors).flat()[0];
    if (first) return first;
  }

  return error.response.data?.message || fallback;
};

// Attach the bearer token to every request.
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Bounce to login on 401.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = `${DASHBOARD_BASE_URL}/login`;
    }
    return Promise.reject(error);
  }
);

// Auth endpoints
export const authAPI = {
  login: (email, password) => api.post('/auth/login', { email, password }),
  logout: () => api.post('/auth/logout'),
  getMe: () => api.get('/auth/me'),
};

// Page endpoints
export const pageAPI = {
  list: (params = {}) => api.get('/pages', { params }),
  get: (pageId) => api.get(`/pages/${pageId}`),
  create: (data) => api.post('/pages', data),
  update: (pageId, data) => api.put(`/pages/${pageId}`, data),
  delete: (pageId) => api.delete(`/pages/${pageId}`),
};

// Media endpoints
export const mediaAPI = {
  list: (params = {}) => api.get('/media', { params }),
  upload: (file, { modelType, modelId, collection, altText, caption } = {}) => {
    const formData = new FormData();
    formData.append('file', file);
    if (modelType) formData.append('model_type', modelType);
    if (modelId) formData.append('model_id', modelId);
    if (collection) formData.append('collection', collection);
    if (altText) formData.append('alt_text', altText);
    if (caption) formData.append('caption', caption);

    return api.post('/media', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  delete: (mediaId) => api.delete(`/media/${mediaId}`),
};

// Publishing — regenerates the static site
export const siteAPI = {
  status: () => api.get('/site/status'),
  build: () => api.post('/site/publish', {}),
};

// SEO endpoints — page-scoped, matching GET|PUT /pages/{page}/seo
export const seoAPI = {
  get: (pageId) => api.get(`/pages/${pageId}/seo`),
  update: (pageId, data) => api.put(`/pages/${pageId}/seo`, data),
};

// Settings endpoints
export const settingsAPI = {
  /**
   * The API returns rows ({key, value, group}) and values may be array-wrapped
   * for historical reasons. Screens want a flat key => string map, so normalise
   * here rather than in every component.
   */
  get: async () => {
    const res = await api.get('/settings');
    const flat = {};

    (res.data.data || []).forEach((row) => {
      const value = Array.isArray(row.value) ? row.value[0] : row.value;
      flat[row.key] = value ?? '';
    });

    return { data: { data: flat } };
  },
  update: (settings) => api.put('/settings', { settings }),
  updateOne: (key, data) => api.put(`/settings/${key}`, data),
  delete: (key) => api.delete(`/settings/${key}`),
};

export default api;
