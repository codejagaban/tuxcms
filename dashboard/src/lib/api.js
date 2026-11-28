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
  revisions: (pageId) => api.get(`/pages/${pageId}/revisions`),
  preview: (pageId) => api.get(`/pages/${pageId}/preview`, { responseType: 'text' }),
};

// The section types a page may hold, with their fields, for its template:
// the built-in ones and those its theme declares.
export const sectionTypeAPI = {
  list: (template) => api.get('/section-types', { params: template ? { template } : {} }),
};

export const jobAPI = {
  list: (params = {}) => api.get('/jobs', { params }),
  get: (id) => api.get(`/jobs/${id}`),
  create: (data) => api.post('/jobs', data),
  update: (id, data) => api.put(`/jobs/${id}`, data),
  delete: (id) => api.delete(`/jobs/${id}`),
};

export const postAPI = {
  list: (params = {}) => api.get('/posts', { params }),
  get: (id) => api.get(`/posts/${id}`),
  create: (data) => api.post('/posts', data),
  update: (id, data) => api.put(`/posts/${id}`, data),
  delete: (id) => api.delete(`/posts/${id}`),
  // The post as the site would show it, from what is in the editor now.
  preview: (data) => api.post('/posts/preview', data, { responseType: 'text', headers: { Accept: 'text/html' } }),
};

export const postTagAPI = {
  list: () => api.get('/post-tags'),
};

export const jobApplicationAPI = {
  list: (params = {}) => api.get('/job-applications', { params }),
  downloadCV: (id) => api.get(`/job-applications/${id}/cv`, { responseType: 'blob' }),
  release: (id) => api.post(`/job-applications/${id}/release`),
};

export const enquiryAPI = {
  list: (params = {}) => api.get('/enquiries', { params }),
  // The kinds of message this site's forms send, as its theme declares them.
  kinds: () => api.get('/enquiry-kinds'),
  release: (id) => api.post(`/enquiries/${id}/release`),
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
  // Settings the site's theme declares in its theme.json, in groups.
  theme: () => api.get('/theme-settings'),
  updateOne: (key, data) => api.put(`/settings/${key}`, data),
  delete: (key) => api.delete(`/settings/${key}`),
};

export default api;
