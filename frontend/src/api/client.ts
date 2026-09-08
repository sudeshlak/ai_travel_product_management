import axios from 'axios'

export const client = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 30_000,
  headers: {
    Accept: 'application/json',
  },
})

client.interceptors.request.use((config) => {
  // Attach auth headers here when auth is added.
  return config
})
