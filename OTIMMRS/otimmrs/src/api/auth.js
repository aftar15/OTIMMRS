import axios from 'axios';

// Update BASE_URL to match your Laravel project structure
const BASE_URL = 'http://10.0.2.2/OTIMMRS/OTIMMRS/OTIMRS_Backend-main/public/api';

export const registerTourist = async (userData) => {
  console.log('Starting Request:', {
    url: `${BASE_URL}/mobile/register`,
    method: 'post',
    data: userData
  });

  const config = {
    method: 'post',
    url: `${BASE_URL}/mobile/register`,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    data: userData,
    timeout: 10000
  };

  try {
    console.log('Making request with config:', config);
    const response = await axios(config);
    console.log('Registration Response:', response.data);
    return response.data;
  } catch (error) {
    console.error('Full error object:', error);
    
    if (error.response) {
      console.error('Error response:', {
        data: error.response.data,
        status: error.response.status,
        headers: error.response.headers
      });
      throw error.response.data;
    } else if (error.request) {
      console.error('No response received. Request:', error.request);
      throw new Error('Network error - no response received. Please check your connection and try again.');
    } else {
      console.error('Error setting up request:', error.message);
      throw error;
    }
  }
};

export const loginTourist = async (credentials) => {
  try {
    const response = await axios({
      method: 'post',
      url: `${BASE_URL}/mobile/login`,
      data: credentials,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      timeout: 10000 // 10 second timeout
    });

    return response.data;
  } catch (error) {
    console.error('Login error:', error);
    if (error.response) {
      console.error('Error response:', error.response.data);
      throw error.response.data;
    } else if (error.request) {
      console.error('No response received:', error.request);
      throw new Error('Network error - no response received. Please check your connection and try again.');
    } else {
      console.error('Error setting up request:', error.message);
      throw error;
    }
  }
};

export const logoutTourist = async (token) => {
  try {
    const response = await axios({
      method: 'post',
      url: `${BASE_URL}/mobile/logout`,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      timeout: 10000 // 10 second timeout
    });

    return response.data;
  } catch (error) {
    console.error('Logout error:', error);
    if (error.response) {
      console.error('Error response:', error.response.data);
      throw error.response.data;
    } else if (error.request) {
      console.error('No response received:', error.request);
      throw new Error('Network error - no response received. Please check your connection and try again.');
    } else {
      console.error('Error setting up request:', error.message);
      throw error;
    }
  }
};
