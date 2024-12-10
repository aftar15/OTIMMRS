// stores/auth.js
import { defineStore } from 'pinia'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: null,
        tourist: null,
        expiresAt: null,
        admin: null,
        adminToken: null,
        adminExpiresAt: null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.tourist,
        getToken: (state) => state.token,
        getTourist: (state) => state.tourist,
        isAdminAuthenticated: (state) => !!state.adminToken && !!state.admin,
        getAdminToken: (state) => state.adminToken,
        getAdmin: (state) => state.admin
    },

    actions: {
        async login(credentials) {
            try {
                const response = await makeRequest.post('/tourist/login', credentials)
                
                if (response.data.success) {
                    this.token = response.data.data.token
                    this.tourist = response.data.data.tourist
                    this.expiresAt = response.data.data.expires_at
                    
                    // Set auth header for future requests
                    makeRequest.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                    
                    return true
                }
                return false
            } catch (error) {
                console.error('Login error:', error)
                toast.error(error.response?.data?.message || 'Login failed')
                return false
            }
        },

        async checkAuth() {
            try {
                if (!this.token) return false
                
                const response = await makeRequest.get('/api/tourist/profile', {
                    validateStatus: status => status < 500
                })

                if (!response.data.success) {
                    this.logout()
                    return false
                }

                return true
            } catch (error) {
                console.error('Auth check failed:', error)
                if (error.response?.status === 401 || error.response?.status === 500) {
                    this.logout()
                }
                return false
            }
        },

        logout() {
            this.token = null
            this.tourist = null
            this.expiresAt = null
            delete makeRequest.defaults.headers.common['Authorization']
        },

        async adminLogin(credentials) {
            try {
                const response = await makeRequest.post('/api/login', {
                    username: credentials.username,
                    password: credentials.password
                });
                
                if (response.data.success) {
                    // Store admin data
                    this.admin = response.data.data.admin;
                    this.adminToken = response.data.data.token || response.data.data.session_id;
                    this.adminExpiresAt = response.data.data.expires_at;
                    
                    // Set auth header for future requests
                    makeRequest.defaults.headers.common['Authorization'] = `Bearer ${this.adminToken}`;
                    
                    // Store in localStorage as backup
                    localStorage.setItem('admin_session', this.adminToken);
                    localStorage.setItem('admin_user', JSON.stringify(this.admin));
                    
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Admin login error:', error);
                toast.error(error.response?.data?.message || 'Admin login failed');
                return false;
            }
        },

        async checkAdminAuth() {
            try {
                // Try to recover from localStorage first
                if (!this.adminToken) {
                    const savedToken = localStorage.getItem('admin_session');
                    const savedAdmin = localStorage.getItem('admin_user');
                    
                    if (savedToken && savedAdmin) {
                        this.adminToken = savedToken;
                        this.admin = JSON.parse(savedAdmin);
                        makeRequest.defaults.headers.common['Authorization'] = `Bearer ${this.adminToken}`;
                    }
                }

                const response = await makeRequest.get('/api/admin/profile');
                
                if (response.data.success) {
                    this.admin = response.data.data;
                    localStorage.setItem('admin_user', JSON.stringify(this.admin));
                    return true;
                }
                
                this.clearAdminAuth();
                return false;
            } catch (error) {
                console.error('Admin auth check failed:', error);
                this.clearAdminAuth();
                return false;
            }
        },

        clearAdminAuth() {
            this.admin = null;
            this.adminToken = null;
            this.adminExpiresAt = null;
            localStorage.removeItem('admin_session');
            localStorage.removeItem('admin_user');
            delete makeRequest.defaults.headers.common['Authorization'];
        }

    },

    persist: true
})
