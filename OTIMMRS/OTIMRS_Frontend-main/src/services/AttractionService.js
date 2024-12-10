import { makeRequest } from '../plugins/axios';

const API_URL = import.meta.env.VITE_API_URL;

export default {
    async getAttractions(page = 1, filters = {}) {
        const params = { page, ...filters };
        const response = await makeRequest.get('/api/admin/attractions', { params });
        return response;
    },

    async getAttraction(id) {
        const response = await makeRequest.get(`/api/admin/attractions/${id}`);
        return response;
    },

    async createAttraction(formData) {
        const response = await makeRequest.post('/api/admin/attractions', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response;
    },

    async updateAttraction(id, formData) {
        const response = await makeRequest.post(`/api/admin/attractions/${id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response;
    },

    async deleteAttraction(id) {
        const response = await makeRequest.delete(`/api/admin/attractions/${id}`);
        return response;
    }
}

