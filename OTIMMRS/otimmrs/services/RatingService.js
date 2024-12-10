import { apiGet, apiPost } from './_api'
import CookieService from './CookieService';

export const RatingService = (() => {
  const baseURL = '/mobile/ratings'

  const getRating = async ({
    type,
    id
  }, setLoading = () => {}) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}/${type}/${id}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.error('Error fetching rating:', error);
      if (typeof setLoading === 'function') {
        setLoading(false);
      }
      throw error;
    }
  }

  const addRating = async ({
    type,
    id,
    rating
  }, setLoading = () => {}) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiPost(`${baseURL}/${type}/${id}`, {
        rating
      }, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.error('Error adding rating:', error);
      if (typeof setLoading === 'function') {
        setLoading(false);
      }
      throw error;
    }
  }

  return {
    getRating,
    addRating
  }
})();