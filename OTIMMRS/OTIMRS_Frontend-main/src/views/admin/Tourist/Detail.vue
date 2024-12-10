<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="main-content">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                    </div>
                </div>
                <div class="profile-container">
                    <a href="#!">
                        Hello, {{ auth.admin.name }}!
                        <img :src="auth.admin.profile_picture || '/user.jpg'" alt="" class="admin-profile">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>Tourist Details</h1>
                    </div>
                </div>
                <div class="tourist-details" v-if="tourist">
                    <div class="detail-card">
                        <div class="profile-section">
                            <h2>{{ tourist.full_name }}</h2>
                            <p class="email">{{ tourist.email }}</p>
                            <img :src="tourist.profile_picture || '/user.jpg'" alt="Tourist Profile" class="profile-image-small">
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Gender:</label>
                                <span>{{ tourist.gender }}</span>
                            </div>
                            <div class="info-item">
                                <label>Nationality:</label>
                                <span>{{ tourist.nationality }}</span>
                            </div>
                            <div class="info-item">
                                <label>Address:</label>
                                <span>{{ tourist.address }}</span>
                            </div>
                            <div class="info-item" v-if="tourist.accommodation_name">
                                <label>Accommodation:</label>
                                <span>{{ tourist.accommodation_name }}</span>
                            </div>
                            <div class="info-item" v-if="tourist.accommodation_location">
                                <label>Location:</label>
                                <span>{{ tourist.accommodation_location }}</span>
                            </div>
                            <div class="info-item" v-if="tourist.accommodation_days">
                                <label>Stay Duration:</label>
                                <span>{{ tourist.accommodation_days }} days</span>
                            </div>
                        </div>
                        <div class="hobbies-section" v-if="tourist.hobbies && tourist.hobbies.length">
                            <h3>Hobbies & Interests</h3>
                            <div class="hobbies-list">
                                <span v-for="hobby in tourist.hobbies" :key="hobby.name" class="hobby-tag">
                                    {{ hobby.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="loading">
                    <div class="spinner"></div>
                    <p>Loading tourist information...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../../stores/auth';
import AdminSidenav from '../../../components/AdminSidenav.vue';
import { makeRequest } from '../../../plugins/axios';
import toast from '../../../plugins/toast';

const route = useRoute();
const auth = useAuthStore();
const tourist = ref(null);

const fetchTouristDetails = async () => {
    try {
        const response = await makeRequest.get(`/tourists/${route.params.id}`);
        tourist.value = response.data.data;
    } catch (error) {
        console.error('Error fetching tourist details:', error);
        toast.error('Failed to load tourist details');
    }
};

onMounted(() => {
    fetchTouristDetails();
});
</script>

<style scoped lang="scss">
.container {
    min-height: 100vh;
    display: flex;
    background-color: #f5f5f5;
}

.main-content {
    flex: 1;
    padding: 2rem;
}

.navbar {
    background: white;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.search-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
}

.input-container {
    flex: 1;
}

.profile-container {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
}

.admin-profile {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-left: 1rem;
}

.page-container {
    padding: 2rem;
    background: #f8f9fa;
    min-height: calc(100vh - 64px);
}

.header {
    margin-bottom: 2rem;
}

.header h1 {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.tourist-details {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.detail-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.profile-image-small {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-section h2 {
    margin: 0.5rem 0;
    color: #333;
    font-size: 1.8rem;
}

.email {
    color: #666;
    font-size: 1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.info-item span {
    color: #333;
    font-size: 1.1rem;
}

.hobbies-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.hobbies-section h3 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.4rem;
}

.hobbies-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.hobby-tag {
    background: #f0f7ff;
    color: #0066cc;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    gap: 1rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f0f0f0;
    border-top: 3px solid #0066cc;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
