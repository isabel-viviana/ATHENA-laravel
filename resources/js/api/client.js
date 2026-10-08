import axios from 'axios';

const baseURL = import.meta.env?.VITE_API_URL ?? '/api';
const TOKEN_KEY = 'athena_token';
const storage = typeof localStorage === 'undefined' ? null : localStorage;

let token = storage ? storage.getItem(TOKEN_KEY) : null;
let onUnauthorized = null;

export function setToken(value) {
  token = value;
  if (!storage) return;
  if (value) {
    storage.setItem(TOKEN_KEY, value);
  } else {
    storage.removeItem(TOKEN_KEY);
  }
}

export function getToken() {
  return token;
}

export function clearToken() {
  setToken(null);
}

export function setOnUnauthorized(handler) {
  onUnauthorized = handler;
}

export const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

api.interceptors.request.use((config) => {
  const current = getToken();
  if (current) {
    config.headers.Authorization = `Bearer ${current}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      clearToken();
      if (typeof onUnauthorized === 'function') {
        onUnauthorized(error);
      }
    }
    return Promise.reject(error);
  },
);

export const request = {
  get: (url, config) => api.get(url, config).then((response) => response.data),
  post: (url, data, config) => api.post(url, data, config).then((response) => response.data),
  put: (url, data, config) => api.put(url, data, config).then((response) => response.data),
  patch: (url, data, config) => api.patch(url, data, config).then((response) => response.data),
  delete: (url, config) => api.delete(url, config).then((response) => response.data),
};
