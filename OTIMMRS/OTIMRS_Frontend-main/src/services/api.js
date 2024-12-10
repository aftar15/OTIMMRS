import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    withCredentials: true
})

// Add request interceptor to attach the token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('admin_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Add response interceptor to handle common errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Handle unauthorized access
            localStorage.removeItem('admin_token')
            localStorage.removeItem('admin_user')
            window.location.href = '/admin/login'
        }
        return Promise.reject(error)
    }
)

export default api
