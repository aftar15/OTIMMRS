import api from '@/services/api'

export default {
  getOverview() {
    return api.get('/reports/overview')
  },

  getAttractionStats(timeframe = 'month') {
    return api.get(`/reports/attractions`, {
      params: { timeframe }
    })
  },

  getActivityStats(timeframe = 'month') {
    return api.get(`/reports/activities`, {
      params: { timeframe }
    })
  },

  getTouristStats(timeframe = 'month') {
    return api.get(`/reports/tourists`, {
      params: { timeframe }
    })
  },

  exportReport(type = 'overview', timeframe = 'month', format = 'csv') {
    return api.get(`/reports/export`, {
      params: { type, timeframe, format },
      responseType: 'blob'
    })
  }
}
