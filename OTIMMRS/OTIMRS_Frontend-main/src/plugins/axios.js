import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const makeRequest = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    timeout: 10000, // 10 seconds timeout
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Add request interceptor
makeRequest.interceptors.request.use(
    config => {
        // For admin routes
        if (config.url.startsWith('/api/admin')) {
            const adminToken = localStorage.getItem('admin_session');
            if (adminToken) {
                config.headers['Authorization'] = `Bearer ${adminToken}`;
            }
        }
        
        // Add CSRF token if available
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token.content;
        }
        
        return config;
    },
    error => {
        console.error('Request error:', error);
        return Promise.reject(error);
    }
);


// Add response interceptor
makeRequest.interceptors.response.use(
    response => response,
    async error => {
        console.error('Response error:', error);
        
        // Handle 401 Unauthorized errors
        if (error.response?.status === 401) {
            const auth = useAuthStore();
            const router = useRouter();
            
            // Clear admin auth if it's an admin route
            if (error.config.url.startsWith('/api/admin')) {
                auth.clearAdminAuth();
                router.push('/admin/login');
            } else {
                auth.logout();
                router.push('/login');
            }
        }
        
        return Promise.reject(error);
    }
);


export default makeRequest
