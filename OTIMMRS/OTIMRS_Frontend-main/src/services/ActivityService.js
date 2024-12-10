import { makeRequest } from '@/plugins/axios'
import CookieService from './CookieService'

export const ActivityService = {
  async getActivities({ page = 1, perPage = 10 } = {}, setLoading = null) {
    try {
      if (setLoading) setLoading(true)
      
      const token = await CookieService.getToken()
      const response = await makeRequest.get('/mobile/activities', {
        params: { page, per_page: perPage },
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (!response.data || typeof response.data !== 'object') {
        console.error('Invalid response:', response.data)
        throw new Error('Invalid response format from server')
      }

      if (!response.data.success) {
        if (response.data.message === 'Unauthorized' || response.data.message === 'Invalid or expired session') {
          throw { logout: true }
        }
        throw new Error(response.data.message || 'Failed to fetch activities')
      }

      return response.data
    } catch (error) {
      console.error('Error in ActivityService.getActivities:', error)
      if (error.logout) {
        throw error
      }
      if (error.response?.status === 401) {
        throw { logout: true }
      }
      throw error
    } finally {
      if (setLoading) setLoading(false)
    }
  }
} 