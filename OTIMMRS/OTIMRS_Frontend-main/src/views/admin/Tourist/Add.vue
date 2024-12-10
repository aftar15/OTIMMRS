<template>
    <div class="container" v-if="auth.admin">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="profile-container">
                    <a href="#!">
                        Hello, {{ auth?.admin?.name || 'Admin' }}!
                        <img :src="auth?.admin?.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>Add Tourist</h1>
                    </div>
                </div>
                <form @submit.prevent="handleSubmit" class="form-container">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" v-model="form.full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" v-model="form.email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" v-model="form.password" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select v-model="form.gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" v-model="form.nationality" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" v-model="form.address" required>
                    </div>
                    <div class="form-group">
                        <label>Interests</label>
                        <div class="hobbies-grid">
                            <div v-for="hobby in hobbyOptions" :key="hobby.id" 
                                 class="hobby-item"
                                 :class="{ selected: selectedHobbies.includes(hobby.id) }"
                                 @click="toggleHobby(hobby.id)">
                                {{ hobby.label }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Accommodation Name</label>
                        <input type="text" v-model="form.accommodation_name" required>
                    </div>
                    <div class="form-group">
                        <label>Accommodation Location</label>
                        <input type="text" v-model="form.accommodation_location" required>
                    </div>
                    <div class="form-group">
                        <label>Accommodation Days</label>
                        <input type="number" v-model="form.accommodation_days" required min="1">
                    </div>
                    <div class="button-group">
                        <button type="button" @click="$router.push('/admin/tourist-information')" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn" :disabled="loading">
                            {{ loading ? 'Adding...' : 'Add Tourist' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-else>
        <router-link to="/admin/login">Please login first</router-link>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminSidenav from '@/components/AdminSidenav.vue'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(false)

onMounted(async () => {
    if (!auth.admin) {
        await auth.checkAdminAuth()
        if (!auth.admin) {
            toast.error('Please login first')
            router.push('/admin/login')
        }
    }
})

const hobbyOptions = [
    { id: 'beach', label: 'Beach & Water Activities', categories: ['beach', 'water_sports'] },
    { id: 'nature', label: 'Nature & Hiking', categories: ['hiking', 'nature', 'camping'] },
    { id: 'culture', label: 'Cultural & Historical', categories: ['museums', 'temples', 'historical'] },
    { id: 'adventure', label: 'Adventure Sports', categories: ['surfing', 'diving', 'climbing'] },
    { id: 'food', label: 'Food & Culinary', categories: ['restaurants', 'food_tours', 'cooking'] },
    { id: 'relaxation', label: 'Relaxation & Wellness', categories: ['spa', 'yoga', 'meditation'] },
    { id: 'nightlife', label: 'Nightlife & Entertainment', categories: ['bars', 'clubs', 'shows'] },
    { id: 'shopping', label: 'Shopping & Markets', categories: ['markets', 'malls', 'souvenirs'] }
]

const form = ref({
    full_name: '',
    email: '',
    password: '',
    gender: '',
    nationality: '',
    address: '',
    accommodation_name: '',
    accommodation_location: '',
    accommodation_days: ''
})

const selectedHobbies = ref([])

const toggleHobby = (hobbyId) => {
    const index = selectedHobbies.value.indexOf(hobbyId)
    if (index === -1) {
        selectedHobbies.value.push(hobbyId)
    } else {
        selectedHobbies.value.splice(index, 1)
    }
}

const handleSubmit = async () => {
    if (selectedHobbies.value.length === 0) {
        toast.error('Please select at least one interest')
        return
    }

    try {
        loading.value = true
        const hobbiesData = selectedHobbies.value.map(id => {
            const hobby = hobbyOptions.find(h => h.id === id)
            return {
                name: hobby.label,
                categories: hobby.categories
            }
        })

        const response = await makeRequest.post('/tourists', {
            ...form.value,
            hobbies: hobbiesData
        })

        if (response.data.success) {
            toast.success('Tourist added successfully')
            router.push('/admin/tourist-information')
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'Error adding tourist')
    } finally {
        loading.value = false
    }
}
</script>

<style scoped lang="scss">
.container {
    min-height: 100vh;
    position: relative;
    background-color: #F2F3F9;
}

.login-container {
    padding-left: 350px;
}

.navbar {
    display: flex;
    justify-content: flex-end;
    padding: 15px;
}

.page-container {
    padding: 30px;
    max-width: 800px;
    margin: auto;
}

.form-container {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    input, select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;

        &:focus {
            outline: none;
            border-color: #2A9DF2;
        }
    }
}

.hobbies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;

    .hobby-item {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;

        &:hover {
            background: rgba(42, 157, 242, 0.1);
        }

        &.selected {
            background: #2A9DF2;
            color: white;
            border-color: #2A9DF2;
        }
    }
}

.button-group {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;

    button {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;

        &:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        &.submit-btn {
            background: #2A9DF2;
            color: white;

            &:hover:not(:disabled) {
                background: darken(#2A9DF2, 10%);
            }
        }

        &.cancel-btn {
            background: #f5f5f5;
            color: #666;

            &:hover {
                background: darken(#f5f5f5, 10%);
            }
        }
    }
}
</style> 