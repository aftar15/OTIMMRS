import { apiGet, apiPost } from './_api'
import CookieService from './CookieService';

export const ActivityService = (() => {
  const baseURL = '/mobile/activities'

  const getActivities = async ({
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

      // Handle both success and error responses
      if (response?.success === true) {
        return {
          success: true,
          data: {
            data: response.data || [],
            total: response.meta?.total || 0
          }
        };
      } else {
        throw new Error(response?.message || 'Failed to get activities');
      }
    } catch (error) {
      console.error('Error in ActivityService.getActivities:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const getRecommendedActivities = async ({
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

      if (response?.success === true) {
        return response;
      } else {
        throw new Error(response?.message || 'Failed to get recommended activities');
      }
    } catch (error) {
      console.error('Error in getRecommendedActivities:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const getRating = async ({
    id
  }, setLoading) => {
    try {
      console.log('ActivityService.getRating called for id:', id);
      const token = await CookieService.getToken();
      const response = await apiGet(`${baseURL}/${id}/rating`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });
      
      console.log('Rating response:', response);

      if (response && (response.success || response.status === 'success')) {
        return {
          success: true,
          data: {
            averageRating: response.data?.average_rating || 0,
            userRating: response.data?.user_rating || null,
            totalRatings: response.data?.total_ratings || 0
          }
        };
      }
      
      throw new Error(response?.message || 'Failed to get rating');
    } catch (error) {
      console.error('Error in ActivityService.getRating:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const addRating = async ({
    id,
    rating
  }, setLoading) => {
    try {
      console.log('ActivityService.addRating called', { id, rating });
      const token = await CookieService.getToken();
      console.log('Token retrieved', { token: token ? 'present' : 'missing' });
      
      const response = await apiPost(`${baseURL}/${id}/rating`, {
        rating
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });
      
      console.log('Rating response:', response);

      if (response && (response.success || response.status === 'success')) {
        return {
          success: true,
          message: response.message || 'Rating added successfully',
          data: response.data
        };
      }
      
      throw new Error(response?.message || 'Failed to add rating');
    } catch (error) {
      console.error('Error in ActivityService.addRating:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const addComment = async ({
    id,
    transportation,
    transportationFee,
    services,
    roadProblems,
    priceIncrease,
    others
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const response = await apiPost(`${baseURL}/${id}/comment`, {
        transportation,
        transportation_fee: parseFloat(transportationFee) || 0,
        services,
        road_problems: roadProblems,
        price_increase: priceIncrease,
        others: others || ''
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      });

      if (response && (response.success || response.status === 'success')) {
        return {
          success: true,
          message: response.message || 'Comment added successfully',
          data: response.data
        };
      }
      
      throw new Error(response?.message || 'Failed to add comment');
    } catch (error) {
      console.error('Error in ActivityService.addComment:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  return {
    getActivities,
    getRecommendedActivities,
    getRating,
    addRating,
    addComment
  }
})()