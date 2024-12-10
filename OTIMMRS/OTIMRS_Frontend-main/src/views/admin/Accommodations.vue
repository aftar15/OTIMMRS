<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                        <!-- <input type="text" placeholder="Search anything...">
                        <i class="material-icons">search</i> -->
                    </div>
                </div>
                <div class="profile-container" v-if="auth.admin">
                    <a href="#!">
                        Hello, {{ auth.admin.name }}!
                        <img :src="auth.admin.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>Tourist Accommodations</h1>
                        <p>{{ accommodations.data?.total || 0 }} accommodation(s)</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/accommodations/add" class="button">
                            <i class="material-icons">add</i>
                            Add Accommodation
                        </router-link>
                    </div>
                </div>
                <div class="performance-grid" v-if="loading">
                    <div class="item-skeleton" v-for="i in 4" :key="i">
                        <div class="image-skeleton"></div>
                    </div>
                </div>
                <div class="performance-grid" v-else>
                    <router-link 
                        v-for="accommodation in accommodations.data?.data" 
                        :key="accommodation.id"
                        :to="`/admin/accommodations/${accommodation.id}`" 
                        class="item"
                    >
                        <div class="image-container" :style="`--image: url(${accommodation.image_url || '/placeholder.jpg'})`">
                            <div class="informations">
                                <div class="texts">
                                    <p class="tag"><i>#{{ accommodation.category || 'Hotel' }}</i></p>
                                    <h1>{{ accommodation.name || 'Tourist Accommodation' }}</h1>
                                    <p class="description">{{ accommodation.description || 'No description available' }}</p>
                                    <StarRatings :item="accommodation" />
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="pagination" v-if="!loading">
                    <button 
                        :disabled="currentPage === 1"
                        @click="changePage(currentPage - 1)"
                        class="page-btn"
                    >
                        Previous
                    </button>
                    <span class="page-info">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button 
                        :disabled="currentPage === totalPages"
                        @click="changePage(currentPage + 1)"
                        class="page-btn"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { makeRequest } from '@/plugins/axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminSidenav from '@/components/AdminSidenav.vue'
import StarRatings from '@/components/StarRatings.vue'
import toast from '@/plugins/toast'

const router = useRouter()
const auth = useAuthStore()

const accommodations = ref({})
const currentPage = ref(1)
const totalPages = ref(1)
const loading = ref(true)

async function changePage(page) {
    try {
        loading.value = true
        const res = await makeRequest.get(`/api/admin/accommodations`, {
            params: {
                page,
                items_per_page: 12
            }
        })
        if (res.data.success) {
            accommodations.value = res.data
            currentPage.value = page
            totalPages.value = Math.ceil(res.data.data.total / 12)
        } else {
            throw new Error(res.data.message || 'Failed to load accommodations')
        }
    } catch (error) {
        console.error('Error changing page:', error)
        toast.error(error.response?.data?.message || 'Error loading accommodations')
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    try {
        loading.value = true
        const res = await makeRequest.get('/api/admin/accommodations', {
            params: {
                items_per_page: 12
            }
        })
        if (res.data.success) {
            accommodations.value = res.data
            totalPages.value = Math.ceil(res.data.data.total / 12)
        } else {
            throw new Error(res.data.message || 'Failed to load accommodations')
        }
    } catch (error) {
        console.error('Error loading accommodations:', error)
        toast.error(error.response?.data?.message || 'Failed to load accommodations')
    } finally {
        loading.value = false
    }
})

async function logOut() {
    try {
        await makeRequest.post('/api/logout')
        auth.admin = null
        localStorage.removeItem('admin_session')
        router.push('/admin')
    } catch (err) {
        console.error('Logout error:', err)
        toast.error('Failed to logout. Please try again.')
    }
}
</script>

<style lang="scss" scoped>
.container {
    display: flex;
    min-height: 100vh;
    background-color: #f5f5f5;
}

.login-container {
    flex: 1;
    padding-left: 350px;
}

.navbar {
    background: white;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-container {
    display: flex;
    align-items: center;
    padding: 0 1rem;
    
    a {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: #333;
        
        img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    }
}

.page-container {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    
    .titles {
        h1 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        
        p {
            margin: 0.5rem 0 0;
            color: #666;
        }
    }
    
    .button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #0066cc;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s;
        
        &:hover {
            background: #0052a3;
        }
        
        i {
            font-size: 1.2rem;
        }
    }
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.item {
    text-decoration: none;
    color: inherit;
    
    .image-container {
        position: relative;
        padding-bottom: 66.67%;
        background: var(--image, #f0f0f0) center/cover no-repeat;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        
        &:hover {
            transform: translateY(-4px);
        }
        
        .informations {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            
            .texts {
                .tag {
                    margin: 0;
                    font-size: 0.9rem;
                    opacity: 0.8;
                }
                
                h1 {
                    margin: 0.5rem 0;
                    font-size: 1.5rem;
                }
                
                .description {
                    margin: 0;
                    opacity: 0.9;
                    font-size: 0.9rem;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
            }
        }
    }
}

.item-skeleton {
    .image-skeleton {
        padding-bottom: 66.67%;
        background: #f0f0f0;
        border-radius: 12px;
        animation: pulse 1.5s infinite;
    }
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    
    .page-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        
        &:hover:not(:disabled) {
            background: #f0f0f0;
        }
        
        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    }
    
    .page-info {
        color: #666;
    }
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>