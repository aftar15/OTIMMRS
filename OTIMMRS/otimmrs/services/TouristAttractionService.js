import { apiGet, apiPost } from './_api'
import CookieService from './CookieService';

export const TouristAttractionService = (() => {
  const baseURL = '/mobile/attractions'

  const getAttractions = async ({
    page = 1,
    perPage = 2
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiGet(`${baseURL}?page=${page}&items_per_page=${perPage}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      // Ensure response has the expected structure
      if (!response || typeof response !== 'object') {
        throw new Error('Invalid response format');
      }

      // Handle both success and error cases
      return {
        success: true,
        data: {
          data: Array.isArray(response.data) ? response.data : [],
          total: response.meta?.total || 0,
          current_page: response.meta?.current_page || page,
          per_page: response.meta?.per_page || perPage
        }
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.getAttractions:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      return {
        success: false,
        message: error.message || 'Failed to fetch attractions',
        error: error.response || error.message,
        data: {
          data: [],
          total: 0,
          current_page: page,
          per_page: perPage
        }
      };
    }
  }

  const getRecommendedAttractions = async ({
    page = 1,
    perPage = 2
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiGet(`${baseURL}/get/recommended?page=${page}&items_per_page=${perPage}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      // Ensure response has the expected structure
      if (!response || typeof response !== 'object') {
        throw new Error('Invalid response format');
      }

      // Handle both success and error cases
      return {
        success: true,
        data: {
          data: Array.isArray(response.data) ? response.data : [],
          total: response.meta?.total || 0,
          current_page: response.meta?.current_page || page,
          per_page: response.meta?.per_page || perPage
        }
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.getRecommendedAttractions:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      return {
        success: false,
        message: error.message || 'Failed to fetch recommended attractions',
        error: error.response || error.message,
        data: {
          data: [],
          total: 0,
          current_page: page,
          per_page: perPage
        }
      };
    }
  }

  const getPopularAttractions = async ({
    page = 1,
    perPage = 2
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiGet(`${baseURL}/get/popular?page=${page}&items_per_page=${perPage}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      // Ensure response has the expected structure
      if (!response || typeof response !== 'object') {
        throw new Error('Invalid response format');
      }

      // Handle both success and error cases
      return {
        success: true,
        data: {
          data: Array.isArray(response.data) ? response.data : [],
          total: response.meta?.total || 0,
          current_page: response.meta?.current_page || page,
          per_page: response.meta?.per_page || perPage
        }
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.getPopularAttractions:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      return {
        success: false,
        message: error.message || 'Failed to fetch popular attractions',
        error: error.response || error.message,
        data: {
          data: [],
          total: 0,
          current_page: page,
          per_page: perPage
        }
      };
    }
  }

  const getRating = async ({ id }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiGet(`${baseURL}/${id}/rating`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      console.log('Get rating response:', response);

      // Backend might return success: true or status: 'success'
      const isSuccess = response?.success || response?.status === 'success';
      
      if (!response || !isSuccess) {
        return {
          success: false,
          message: response?.message || 'Failed to fetch rating',
          data: {
            average_rating: 0,
            user_rating: null,
            total_ratings: 0
          }
        };
      }

      return {
        success: true,
        data: {
          average_rating: response.data?.average_rating || 0,
          user_rating: response.data?.user_rating || null,
          total_ratings: response.data?.total_ratings || 0
        }
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.getRating:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      return {
        success: false,
        message: error.message || 'Failed to fetch rating',
        error: error.response || error.message,
        data: {
          average_rating: 0,
          user_rating: null,
          total_ratings: 0
        }
      };
    }
  }

  const addRating = async ({ id, rating }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiPost(`${baseURL}/${id}/rating`, { rating }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      console.log('Add rating response:', response);

      // Backend might return success: true or status: 'success'
      const isSuccess = response?.success || response?.status === 'success' || 
                       response?.message?.includes('successfully');
      
      if (!response || !isSuccess) {
        return {
          success: false,
          message: response?.message || 'Failed to add rating'
        };
      }

      return {
        success: true,
        message: response.message || 'Rating added successfully',
        data: response.data || {}
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.addRating:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      
      // Check if error message indicates success (backend inconsistency)
      if (error.message?.includes('successfully')) {
        return {
          success: true,
          message: error.message,
          data: {}
        };
      }
      
      return {
        success: false,
        message: error.message || 'Failed to add rating',
        error: error.response || error.message
      };
    }
  }

  const addComment = async (payload, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const { 
        id, 
        transportation,
        transportation_fee,
        services,
        road_problems,
        price_increase,
        others 
      } = payload;

      if (!id) {
        return {
          success: false,
          message: 'Attraction ID is required'
        };
      }

      if (!transportation || !transportation_fee || !services || !road_problems || !price_increase) {
        return {
          success: false,
          message: 'Please fill in all required fields'
        };
      }

      // Send comment to the correct endpoint with all fields
      const response = await apiPost(`/mobile/attractions/${id}/comment`, { 
        comment: `Comment for ${id}`,  // Required field
        transportation,
        transportation_fee: Number(transportation_fee),
        services,
        road_problems,
        price_increase,
        others: others || ''
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      console.log('Add comment API response:', response);

      // Check if response is already parsed
      if (typeof response === 'string') {
        try {
          response = JSON.parse(response);
        } catch (parseError) {
          console.error('Error parsing response:', parseError);
          return {
            success: false,
            message: 'Failed to parse server response'
          };
        }
      }

      // Backend might return success: true or status: 'success'
      const isSuccess = response?.success || response?.status === 'success' || 
                       response?.message?.includes('successfully');
      
      if (!response || !isSuccess) {
        return {
          success: false,
          message: response?.error || response?.message || 'Failed to add comment'
        };
      }

      return {
        success: true,
        message: response.message || 'Comment added successfully',
        data: response.data || {}
      };
    } catch (error) {
      console.error('Error in TouristAttractionService.addComment:', error);
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        response: error.response
      });
      if (setLoading) setLoading(false);
      
      // Try to extract error message from response
      let errorMessage = 'Failed to add comment';
      if (error.response) {
        try {
          const errorData = typeof error.response === 'string' ? 
            JSON.parse(error.response) : error.response;
          errorMessage = errorData.error || errorData.message || errorMessage;
        } catch (parseError) {
          console.error('Error parsing error response:', parseError);
        }
      }
      
      return {
        success: false,
        message: errorMessage,
        error: error.response || error.message
      };
    }
  }

  return {
    getAttractions,
    getRecommendedAttractions,
    getPopularAttractions,
    getRating,
    addRating,
    addComment
  }
})();