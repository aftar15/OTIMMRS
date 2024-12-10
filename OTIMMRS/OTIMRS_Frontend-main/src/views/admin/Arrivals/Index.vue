<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="content-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                        <input type="text" v-model="searchQuery" placeholder="Search arrivals...">
                        <i class="material-icons">search</i>
                    </div>
                </div>
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
                        <h1>Arrivals</h1>
                        <p>Manage tourist arrivals</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/arrivals/add" class="button">
                            <i class="material-icons">add</i>
                            Add New Arrival
                        </router-link>
                    </div>
                </div>
                <div v-if="isLoading" class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading arrivals...</p>
                </div>
                <div v-else class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tourist</th>
                                <th>Arrival Date</th>
                                <th>Departure Date</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="arrival in filteredArrivals" :key="arrival.id">
                                <td>{{ arrival.tourist_name }}</td>
                                <td>{{ formatDate(arrival.arrival_date) }}</td>
                                <td>{{ formatDate(arrival.departure_date) }}</td>
                                <td>{{ arrival.purpose_of_visit }}</td>
                                <td>
                                    <span :class="['status', arrival.status]">
                                        {{ arrival.status.charAt(0).toUpperCase() + arrival.status.slice(1) }}
                                    </span>
                                </td>
                                <td>
                                    <router-link :to="`/admin/arrivals/${arrival.id}/edit`" class="icon-button" title="Edit">
                                        <i class="material-icons">edit</i>
                                    </router-link>
                                    <button @click="deleteArrival(arrival.id)" class="icon-button" title="Delete">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredArrivals.length === 0">
                                <td colspan="6" class="empty-state">
                                    <i class="material-icons">info</i>
                                    <p>No arrivals found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { makeRequest } from '@/plugins/axios'
import AdminSidenav from '@/components/AdminSidenav.vue'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const isLoading = ref(true)
const arrivals = ref([])
const searchQuery = ref('')

const filteredArrivals = computed(() => {
    if (!arrivals.value || !Array.isArray(arrivals.value)) return []
    if (!searchQuery.value) return arrivals.value
    
    const query = searchQuery.value.toLowerCase()
    return arrivals.value.filter(arrival => {
        if (!arrival) return false
        return (
            arrival.tourist_name?.toLowerCase().includes(query) ||
            arrival.purpose_of_visit?.toLowerCase().includes(query) ||
            arrival.status?.toLowerCase().includes(query)
        )
    })
})

const loadArrivals = async () => {
    try {
        isLoading.value = true
        arrivals.value = [] // Reset arrivals before loading
        const response = await makeRequest.get('/api/admin/arrivals')
        if (response.data.success) {
            arrivals.value = response.data.data || []
        } else {
            toast.error(response.data.message || 'Failed to load arrivals')
            arrivals.value = []
        }
    } catch (error) {
        console.error('Failed to load arrivals:', error)
        toast.error(error.response?.data?.message || 'Failed to load arrivals')
        arrivals.value = []
    } finally {
        isLoading.value = false
    }
}

const deleteArrival = async (id) => {
    if (!confirm('Are you sure you want to delete this arrival?')) {
        return
    }
    
    try {
        const response = await makeRequest.delete(`/api/admin/arrivals/${id}`)
        if (response.data.success) {
            toast.success('Arrival deleted successfully')
            await loadArrivals()
        } else {
            toast.error(response.data.message || 'Failed to delete arrival')
        }
    } catch (error) {
        console.error('Failed to delete arrival:', error)
        toast.error(error.response?.data?.message || 'Failed to delete arrival')
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    loadArrivals()
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
    align-items: center;
    padding: 15px 30px;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

    .search-container {
        flex: 1;

        .input-container {
            position: relative;
            max-width: 500px;

            input {
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

            i {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #939393;
            }
        }
    }

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
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        p {
            color: #666;
            font-size: 14px;
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
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;

            &:hover {
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

.table-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow-x: auto;

    table {
        width: 100%;
        border-collapse: collapse;
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #E5E5E5;
        }

        th {
            font-weight: 600;
            color: #666;
            background: #f9f9f9;
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;

            &.pending {
                background-color: #FFF3DC;
                color: #FFB82E;
            }

            &.confirmed {
                background-color: #E1F7E3;
                color: #1AB759;
            }

            &.cancelled {
                background-color: #FFE5E5;
                color: #FF4D4D;
            }

            &.completed {
                background-color: #E5E5E5;
                color: #666666;
            }
        }

        .icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            color: #666;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            margin: 0 4px;
            border: none;
            cursor: pointer;
            background: none;

            &:hover {
                background-color: rgba(0, 0, 0, 0.05);
                color: #2A9DF2;
            }

            i {
                font-size: 18px;
            }
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;

            i {
                font-size: 48px;
                color: #ccc;
                margin-bottom: 10px;
            }

            p {
                margin: 0;
            }
        }
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>