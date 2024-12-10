<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="content-container">
            <div class="navbar">
                <div class="profile-container">
                    <a href="#!">
                        Hello, {{ auth.admin?.name || 'Admin' }}!
                        <img :src="auth.admin?.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>
                            <router-link to="/admin/arrivals" class="icon-button">
                                <i class="material-icons">arrow_back</i>
                            </router-link>
                            Add New Arrival
                        </h1>
                    </div>
                    <div class="action">
                        <button @click="saveArrival" :disabled="isSubmitting" class="button">
                            <i class="material-icons">save</i>
                            {{ isSubmitting ? 'Saving...' : 'Save Arrival' }}
                        </button>
                    </div>
                </div>
                <div v-if="isLoading" class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading data...</p>
                </div>
                <div v-else class="form-container">
                    <h2 class="subheader">Arrival Information</h2>
                    <div class="input-group">
                        <div class="input-container one-third">
                            <select v-model="arrival.tourist_id" required>
                                <option value="">Select Tourist</option>
                                <option v-for="tourist in tourists" :key="tourist?.id" :value="tourist?.id">
                                    {{ tourist?.full_name }}
                                </option>
                            </select>
                            <i class="material-icons">person</i>
                        </div>
                        <div class="input-container one-third">
                            <select v-model="arrival.accommodation_id">
                                <option value="">Select Accommodation (Optional)</option>
                                <option v-for="accommodation in accommodations" :key="accommodation?.id" :value="accommodation?.id">
                                    {{ accommodation?.name }}
                                </option>
                            </select>
                            <i class="material-icons">hotel</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.arrival_date" type="datetime-local" placeholder="Arrival Date" required>
                            <i class="material-icons">event</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.departure_date" type="datetime-local" placeholder="Departure Date" required>
                            <i class="material-icons">event</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.purpose_of_visit" type="text" placeholder="Purpose of Visit" required>
                            <i class="material-icons">info</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.transportation_mode" type="text" placeholder="Transportation Mode" required>
                            <i class="material-icons">directions_car</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.number_of_companions" type="number" placeholder="Number of Companions" min="0" required>
                            <i class="material-icons">group</i>
                        </div>
                        <div class="input-container one-third">
                            <select v-model="arrival.status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                            <i class="material-icons">flag</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.contact_number" type="tel" placeholder="Contact Number" required>
                            <i class="material-icons">phone</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.emergency_contact" type="text" placeholder="Emergency Contact" required>
                            <i class="material-icons">contact_phone</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="arrival.emergency_contact_number" type="tel" placeholder="Emergency Contact Number" required>
                            <i class="material-icons">phone</i>
                        </div>
                        <div class="input-container full-width">
                            <textarea v-model="arrival.notes" placeholder="Notes"></textarea>
                            <i class="material-icons">note</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { makeRequest } from '@/plugins/axios'
import AdminSidenav from '@/components/AdminSidenav.vue'
import toast from '@/plugins/toast'

const router = useRouter()
const auth = useAuthStore()
const isLoading = ref(true)
const isSubmitting = ref(false)
const tourists = ref([])
const accommodations = ref([])
const arrival = ref({
    tourist_id: '',
    accommodation_id: '',
    arrival_date: '',
    departure_date: '',
    purpose_of_visit: '',
    transportation_mode: '',
    number_of_companions: 0,
    status: 'pending',
    contact_number: '',
    emergency_contact: '',
    emergency_contact_number: '',
    notes: ''
})

const loadData = async () => {
    try {
        isLoading.value = true
        const [touristsResponse, accommodationsResponse] = await Promise.all([
            makeRequest.get('/tourists'),
            makeRequest.get('/accommodations')
        ])
        
        if (touristsResponse.data.success) {
            tourists.value = touristsResponse.data.data || []
        }
        
        if (accommodationsResponse.data.success) {
            accommodations.value = accommodationsResponse.data.data || []
        }
    } catch (error) {
        console.error('Failed to load data:', error)
        toast.error('Failed to load required data')
        tourists.value = []
        accommodations.value = []
    } finally {
        isLoading.value = false
    }
}

const saveArrival = async () => {
    if (isSubmitting.value) return

    try {
        isSubmitting.value = true
        const response = await makeRequest.post('/admin/arrivals', arrival.value)
        
        if (response.data.success) {
            toast.success('Arrival created successfully')
            router.push('/admin/arrivals')
        } else {
            throw new Error(response.data.message || 'Failed to create arrival')
        }
    } catch (error) {
        console.error('Failed to create arrival:', error)
        toast.error(error.response?.data?.message || 'Failed to create arrival')
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    loadData()
})
</script>

<style scoped lang="scss">
.container {
    min-height: 100vh;
    position: relative;
    background-color: #F2F3F9;
    display: flex;
}

.content-container {
    flex: 1;
    padding-left: 350px;
}

.navbar {
    display: flex;
    justify-content: flex-end;
    padding: 15px 30px;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

    .profile-container {
        a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;

            &:hover {
                background: rgba(0, 0, 0, 0.05);
            }

            img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
            }
        }
    }
}

.page-container {
    padding: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    
    .titles {
        h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 600;

            .icon-button {
                color: #666;
                text-decoration: none;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 8px;
                transition: all 0.3s;

                &:hover {
                    background: rgba(0, 0, 0, 0.05);
                }
            }
        }
    }

    .action {
        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #2A9DF2;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;

            &:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }

            &:not(:disabled):hover {
                background-color: darken(#2A9DF2, 10%);
            }

            i {
                font-size: 20px;
            }
        }
    }
}

.loading-container {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2A9DF2;
        border-radius: 50%;
        margin: 0 auto 20px;
        animation: spin 1s linear infinite;
    }

    p {
        color: #666;
    }
}

.form-container {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

    .subheader {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }
}

.input-group {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.input-container {
    position: relative;
    flex: 1;
    min-width: 300px;

    &.one-third {
        flex-basis: calc(33.333% - 20px);
    }

    &.full-width {
        flex-basis: 100%;
    }

    input, select, textarea {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: 1px solid #E5E5E5;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;

        &:focus {
            outline: none;
            border-color: #2A9DF2;
            box-shadow: 0 0 0 3px rgba(42, 157, 242, 0.1);
        }
    }

    textarea {
        height: 100px;
        resize: vertical;
        padding-top: 40px;
    }

    i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #939393;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style> 