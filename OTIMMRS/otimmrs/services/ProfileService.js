import { apiGet } from './_api'
import CookieService from './CookieService';

export const ProfileService = (() => {
  const baseURL = '/profile'

  const getProfile = async (setLoading) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}`, {
        'Authorization': token,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      alert(error.error);
      setLoading(false);
      throw error;
    }
  }

  return {
    getProfile
  }
})()