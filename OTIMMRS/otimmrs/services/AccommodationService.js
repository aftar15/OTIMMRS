import { apiGet, apiPost } from './_api';
import CookieService from './CookieService';

export const AccommodationService = (() => {
  const baseURL = '/mobile/accommodations'

  const getAccommodations = async ({
    page = 1,
    perPage = 2
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}?page=${page}&items_per_page=${perPage}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.error('Error in AccommodationService.getAccommodations:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const getRecommendedAccommodations = async ({
    page = 1,
    perPage = 2
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}/recommended?page=${page}&items_per_page=${perPage}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.error('Error in AccommodationService.getRecommendedAccommodations:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const getRating = async ({
    id
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}/${id}/rating`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.error('Error in AccommodationService.getRating:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const addRating = async ({
    id,
    rating
  }, setLoading) => {
    try {
      console.log('AccommodationService.addRating called', { id, rating });
      const token = await CookieService.getToken();
      console.log('Token retrieved', { token: token ? 'present' : 'missing' });
      
      const response = await apiPost(`${baseURL}/${id}/rating`, {
        rating
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      console.log('Rating response received', response);
      
      if (response.status === 'success' || response.message === 'Rating added successfully') {
        return { success: true, data: response.data };
      } else {
        throw new Error(response.message || 'Failed to add rating');
      }
    } catch (error) {
      console.error('Error in AccommodationService.addRating:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  const addComment = async (payload, setLoading) => {
    try {
      console.log('AccommodationService.addComment called with:', payload);

      const token = await CookieService.getToken();
      const response = await apiPost(`${baseURL}/comment`, {
        accommodation_id: payload.accommodation_id,
        comment: [
          `Transportation: ${payload.transportation}`,
          `Transportation Fee: ${payload.transportation_fee}`,
          `Services: ${payload.services}`,
          `Road Problems: ${payload.road_problems}`,
          `Price Increase: ${payload.price_increase}`,
          payload.others ? `Additional Comments: ${payload.others}` : ''
        ].filter(Boolean).join('\n'),
        transportation: payload.transportation,
        transportation_fee: payload.transportation_fee,
        services: payload.services,
        road_problems: payload.road_problems,
        price_increase: payload.price_increase,
        others: payload.others
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      
      console.log('Comment response:', response);
      
      if (response.status === 'success' || response.message === 'Comment added successfully') {
        return { success: true, data: response.data };
      } else {
        return response;
      }
    } catch (error) {
      console.error('Error in AccommodationService.addComment:', error);
      if (setLoading) setLoading(false);
      throw error;
    }
  }

  return {
    getAccommodations,
    getRecommendedAccommodations,
    getRating,
    addRating,
    addComment
  }
})();
