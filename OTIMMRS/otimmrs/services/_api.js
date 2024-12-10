// const API_BASE_URL = 'http://46.137.215.227/api';
// const API_BASE_URL = 'http://10.0.2.2/OTIMMRS/OTIMMRS/OTIMRS_Backend-main/public';
export const API_BASE_URL = 'https://otimmrs.site/api';

// Helper function to get full image URL
export const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://i.imgur.com/8dKKrN2.jpg'; // Default image
  if (imagePath.startsWith('http')) return imagePath;
  return `${API_BASE_URL}/storage/${imagePath}`;
};

const defaultHeaders = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
};

const handleResponse = async (response) => {
  const contentType = response.headers.get('content-type');
  
  if (!contentType || !contentType.includes('application/json')) {
    throw new Error('Invalid response format: Expected JSON');
  }

  const text = await response.text();
  
  try {
    const data = JSON.parse(text);
    
    if (!response.ok) {
      throw data;
    }
    
    return data;
  } catch (error) {
    console.error('JSON Parse Error:', error);
    console.error('Response Text:', text);
    throw new Error('Failed to parse response as JSON');
  }
};

export const apiGet = async (endpoint, headers = { ...defaultHeaders }) => {
  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    method: 'GET',
    headers
  });
  return handleResponse(response);
};

export const apiPost = async (endpoint, data, headers = {}) => {
  try {
    console.log('Starting POST request:', { endpoint, data });
    
    const response = await fetch(`${API_BASE_URL}/api${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...headers
      },
      body: JSON.stringify(data)
    });

    console.log('Response status:', response.status);
    const responseText = await response.text();
    console.log('Response text:', responseText);

    try {
      const jsonData = JSON.parse(responseText);
      return jsonData;
    } catch (parseError) {
      console.error('Error parsing response:', parseError);
      throw new Error('Failed to parse server response');
    }
  } catch (error) {
    console.error('API error:', error);
    throw error;
  }
};

export const apiPut = async (endpoint, body, headers = { ...defaultHeaders }) => {
  const response = await fetch(`${API_BASE_URL}/api${endpoint}`, {
    method: 'PUT',
    headers,
    body: JSON.stringify(body),
  });
  return handleResponse(response);
};

export const apiDelete = async (endpoint, headers = { ...defaultHeaders }) => {
  const response = await fetch(`${API_BASE_URL}/api${endpoint}`, {
    method: 'DELETE',
    headers
  });
  return handleResponse(response);
};
