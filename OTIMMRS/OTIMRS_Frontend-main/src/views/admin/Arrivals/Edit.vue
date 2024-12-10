<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                    </div>
                </div>
                <div class="profile-container">
                    <a href="#!">
                        Hello, {{ auth.admin.first_name }}!
                        <img :src="auth.admin.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>
                            <a href="/admin/arrivals" class="icon-button">
                                <i class="material-icons">
                                    arrow_back
                                </i>
                            </a>
                            {{ isLoading ? 'Loading...' : arrival.tourist_name }}
                        </h1>
                    </div>
                    <div class="action">
                        <button class="icon-button" @click="deleteArrival()" title="Delete Arrival">
                            <i class="material-icons">delete</i>
                        </button>
                        <button @click="saveArrival()" class="button">
                            <i class="material-icons">save</i>
                            Save Changes
                        </button>
                    </div>
                </div>
                <div v-if="isLoading" class="skeleton" style="text-align:center">
                    Loading...
                </div>
                <div v-else>
                    <h2 class="subheader">Arrival Information</h2>
                    <div class="input-group">
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
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { makeRequest } from '@/plugins/axios'
import AdminSidenav from '@/components/AdminSidenav.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const isLoading = ref(true)
const arrival = ref({
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

const loadArrival = async () => {
    try {
        isLoading.value = true
        const response = await makeRequest.get(`/admin/arrivals/${route.params.id}`)
        const data = response.data.data

        // Format dates for datetime-local input
        arrival.value = {
            ...data,
            arrival_date: data.arrival_date.slice(0, 16), // Remove seconds
            departure_date: data.departure_date.slice(0, 16) // Remove seconds
        }
    } catch (error) {
        console.error('Failed to load arrival:', error)
        alert('Failed to load arrival details')
    } finally {
        isLoading.value = false
    }
}

const saveArrival = async () => {
    try {
        await makeRequest.put(`/admin/arrivals/${route.params.id}`, arrival.value)
        alert('Arrival updated successfully')
        router.push('/admin/arrivals')
    } catch (error) {
        console.error('Failed to update arrival:', error)
        alert('Failed to update arrival')
    }
}

const deleteArrival = async () => {
    if (!confirm('Are you sure you want to delete this arrival?')) {
        return
    }
    
    try {
        await makeRequest.delete(`/admin/arrivals/${route.params.id}`)
        alert('Arrival deleted successfully')
        router.push('/admin/arrivals')
    } catch (error) {
        console.error('Failed to delete arrival:', error)
        alert('Failed to delete arrival')
    }
}

onMounted(() => {
    loadArrival()
})
</script>

<style scoped lang="scss">
.container {
    min-height: 100vh;
    position: relative;
    background-color: #F2F3F9;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 50px;
    
    .titles {
        h1 {
            font-size: 25px;
            margin-top: 30px;
        }
        
        p {
            color: #939393;
        }
    }

    .action {
        .button {
            display: inline-block;
            background-color: #2A9DF2;
            color: #F2F3F9;
            padding: 13px 20px;
            border-radius: 7px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.082);
            text-decoration: none;
            transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 0;
            margin: 0 10px;
            cursor: pointer;

            &:hover {
                opacity: 0.8;
            }

            &:active {
                transform: scale(0.9);
            }
        }
        
        .icon-button {
            display: inline-block;
            color: #c73b3b;
            padding: 13px;
            border-radius: 50%;
            text-decoration: none;
            transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 0;
            cursor: pointer;

            &:hover {
                background-color: rgba(199, 59, 59, 0.1);
            }

            &:active {
                transform: scale(0.9);
            }
        }
    }
}

.input-group {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
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
        padding: 15px 20px;
        padding-left: 50px;
        border: 1px solid #E5E5E5;
        border-radius: 7px;
        font-size: 14px;
        transition: all .3s ease;

        &:focus {
            outline: none;
            border-color: #2A9DF2;
        }
    }

    textarea {
        height: 100px;
        resize: vertical;
    }

    i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #939393;
    }
}
</style> 