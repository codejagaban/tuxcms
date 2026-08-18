import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api/v1';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

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
      window.location.href = '/login';
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
  build: () => api.post('/site/build'),
};

// SEO endpoints — page-scoped, matching GET|PUT /pages/{page}/seo
export const seoAPI = {
  get: (pageId) => api.get(`/pages/${pageId}/seo`),
  update: (pageId, data) => api.put(`/pages/${pageId}/seo`, data),
};

// Settings endpoints
export const settingsAPI = {
  list: () => api.get('/settings'),
  grouped: () => api.get('/settings/grouped'),
  update: (key, data) => api.put(`/settings/${key}`, data),
  create: (data) => api.post('/settings', data),
  delete: (key) => api.delete(`/settings/${key}`),
};

export default api;
