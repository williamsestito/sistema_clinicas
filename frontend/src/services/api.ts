import axios, { AxiosError } from 'axios'
import type {
  AxiosInstance,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from 'axios'

// Criação da instância principal da API
const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api',
  withCredentials: true,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// ============================
// 🔐 INTERCEPTOR DE REQUISIÇÃO
// ============================
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error: AxiosError) => Promise.reject(error)
)

// ============================
// ⚙️ INTERCEPTOR DE RESPOSTA
// ============================
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error: AxiosError) => {
    if (error.response) {
      const { status } = error.response

      switch (status) {
        case 401:
          console.warn('Sessão expirada ou não autorizada.')
          localStorage.removeItem('token')
          window.location.href = '/login'
          break
        case 403:
          console.error('Acesso negado.')
          break
        case 500:
          console.error('Erro interno no servidor.')
          break
        default:
          console.error(`Erro ${status}:`, error.message)
      }
    } else {
      console.error('Falha de conexão com o servidor.')
    }

    return Promise.reject(error)
  }
)

export default api
