import axios from 'axios';
import CookieService from './CookieService';

// Update API URL to use local network IP and correct path
const API_URL = 'http://192.168.1.5/OTIMRS_Backend-main/public/api';

// Create axios instance with updated config
const apiClient = axios.create({
  baseURL: API_URL,
  timeout: 10000, // Reduced timeout to 10 seconds
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add request interceptor
apiClient.interceptors.request.use(async request => {
  console.log('Starting Request:', {
    url: request.url,
    method: request.method,
    data: request.data,
    baseURL: request.baseURL
  });
  return request;
}, error => {
  console.error('Request Error:', error);
  return Promise.reject(error);
});

// Add response interceptor
apiClient.interceptors.response.use(
  response => response,
  error => {
    console.error('Response Error:', {
      message: error.message,
      code: error.code,
      response: error.response,
      config: error.config
    });

    if (error.code === 'ERR_NETWORK') {
      throw new Error('Network connection failed. Please check if the server is accessible.');
    }
    
    if (error.response?.status === 401) {
      CookieService.removeToken();
    }
    
    throw error;
  }
);

class AuthService {
  async register(userData) {
    try {
      // Map frontend field names to backend field names
      const mappedData = {
        full_name: userData.fullName,
        email: userData.email,
        password: userData.password,
        gender: userData.gender,
        nationality: userData.nationality,
        address: userData.address,
        hobbies: userData.hobbies ? 
          [{ 
            name: userData.hobbies[0]?.name || 'Beach & Water Activities', 
            categories: userData.hobbies[0]?.categories || ['beach', 'water_sports'] 
          }] : 
          []
      };

      console.log('Sending registration data:', mappedData);
      console.log('Full API URL:', `${apiClient.defaults.baseURL}/mobile/register`);

      try {
        const response = await apiClient.post('/mobile/register', mappedData);
        
        if (response.data && response.data.token) {
          await CookieService.setToken(response.data.token);
        }
        
        return response.data;
      } catch (axiosError) {
        console.error('Axios Error Details:', {
          response: axiosError.response,
          request: axiosError.request,
          config: axiosError.config,
          message: axiosError.message,
          code: axiosError.code,
          status: axiosError.response?.status,
          data: axiosError.response?.data
        });
        
        throw axiosError;
      }
    } catch (error) {
      console.error('Registration Error:', error);
      
      if (error.code === 'ERR_NETWORK') {
        throw new Error('Server is not accessible. Please try again later or contact support.');
      } else if (error.response?.data?.message) {
        throw new Error(error.response.data.message);
      } else {
        throw new Error('Registration failed. Please check your connection and try again.');
      }
    }
  }

  async login({ email, password }) {
    try {
      const response = await apiClient.post('/mobile/login', { email, password });
      
      if (response.data && response.data.token) {
        await CookieService.setToken(response.data.token);
      }
      
      return response.data;
    } catch (error) {
      if (error.response) {
        throw new Error(error.response.data.message || 'Invalid credentials');
      } else if (error.request) {
        throw new Error('Unable to connect to server. Please check your internet connection.');
      } else {
        throw new Error('An error occurred while logging in.');
      }
    }
  }

  async getProfile() {
    try {
      const response = await apiClient.get('/mobile/profile');
      return response.data.data;
    } catch (error) {
      if (error.response) {
        throw new Error(error.response.data.message || 'Failed to get profile');
      } else if (error.request) {
        throw new Error('Unable to connect to server. Please check your internet connection.');
      } else {
        throw new Error('An error occurred while fetching profile.');
      }
    }
  }

  async checkAuth() {
    try {
      const token = await CookieService.getToken();
      if (!token) {
        return false;
      }

      const response = await apiClient.get('/mobile/profile');
      return response.data.success;
    } catch (error) {
      console.error('Auth check failed:', error);
      return false;
    }
  }

  async logout() {
    try {
      await apiClient.post('/mobile/logout');
      await CookieService.removeToken();
    } catch (error) {
      console.error('Logout error:', error);
      await CookieService.removeToken();
      throw new Error('Error during logout');
    }
  }
}

export default new AuthService();