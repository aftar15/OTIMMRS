import { apiGet } from './_api'
import CookieService from './CookieService';

export const SearchService = (() => {
  const baseURL = '/search'

  const search = async ({
    search = '',
    page = 1
  }, setLoading) => {
    try {
      const token = await CookieService.getToken();
      const data = await apiGet(`${baseURL}?search=${search}&page=${page}`, {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      });
      return data;
    } catch (error) {
      console.log('Search error:', error);
      setLoading(false);
      throw error;
    }
  }

  return {
    search
  }
})()