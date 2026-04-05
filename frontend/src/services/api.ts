import axios, { AxiosError } from 'axios'
import type { AxiosInstance, AxiosResponse } from 'axios'

let refreshing = false
let queue: Array<(token: string) => void> = []

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api',
  withCredentials: true,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// REQUEST
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// RESPONSE
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError & { config: any }) => {
    const original = error.config

    if (error.response?.status === 401 && !original._retry) {
      if (refreshing) {
        return new Promise((resolve) => {
          queue.push((token: string) => {
            original.headers.Authorization = `Bearer ${token}`
            resolve(api(original))
          })
        })
      }

      original._retry = true
      refreshing = true

      try {
        await axios.get(`${import.meta.env.VITE_API_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true })

        const newToken = localStorage.getItem('token')

        queue.forEach(cb => cb(newToken as string))
        queue = []
        refreshing = false

        original.headers.Authorization = `Bearer ${newToken}`
        return api(original)

      } catch (_err) {
        refreshing = false
        queue = []
        localStorage.removeItem('token')
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  }
)

export default api
