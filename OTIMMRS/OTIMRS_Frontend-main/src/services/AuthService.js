import api from './api'

class AuthService {
  static async login({ email, password }) {
    try {
      const response = await api.post('/admin/login', {
        email,
        password
      })

      if (response.data && response.data.success) {
        this.setToken(response.data.token)
        this.setUser(response.data.user)
        return response.data
      } else {
        throw new Error(response.data.message || 'Login failed')
      }
    } catch (error) {
      console.error('Login error in service:', error)
      throw error
    }
  }

  static setToken(token) {
    localStorage.setItem('admin_token', token)
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  static getToken() {
    return localStorage.getItem('admin_token')
  }

  static setUser(user) {
    localStorage.setItem('admin_user', JSON.stringify(user))
  }

  static getUser() {
    const user = localStorage.getItem('admin_user')
    return user ? JSON.parse(user) : null
  }

  static isAuthenticated() {
    return !!this.getToken()
  }

  static logout() {
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')
    delete api.defaults.headers.common['Authorization']
  }

  static async checkAuth() {
    try {
      const response = await api.get('/admin/profile')
      return response.data
    } catch (error) {
      this.logout()
      throw error
    }
  }

  static initializeAuth() {
    const token = this.getToken()
    if (token) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    }
  }
}

export default AuthService
