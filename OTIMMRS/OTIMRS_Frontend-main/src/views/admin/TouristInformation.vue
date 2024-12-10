<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                        <input type="text" v-model="searchQuery" placeholder="Search tourists...">
                        <i class="material-icons">search</i>
                    </div>
                </div>
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
                        <h1>Tourist Information</h1>
                        <p>{{ tourists != 'loading...' ? tourists.length : '0' }} tourist(s)</p>
                    </div>
                    <div class="actions">
                        <router-link to="/admin/tourists/add" class="add-btn">
                            <i class="material-icons">add</i>
                            Add Tourist
                        </router-link>
                    </div>
                </div>
                <div class="performance-grid" v-if="tourists == 'loading...'">
                    <a href="#!" class="item skeleton" v-for="i in 4" :key="i">
                        <svg
                            role="img"
                            width="236"
                            height="261"
                            aria-labelledby="loading-aria"
                            viewBox="0 0 236 261"
                            preserveAspectRatio="none"
                            >
                            <title id="loading-aria">Loading...</title>
                            <rect
                                x="0"
                                y="0"
                                width="100%"
                                height="100%"
                                clip-path="url(#clip-path)"
                                style='fill: url("#fill");'
                            ></rect>
                            <defs>
                                <clipPath id="clip-path">
                                    <path d="M 52 0 h 132 v 132 H 52 z" /> 
                                    <rect x="0" y="164" rx="6" ry="6" width="236" height="29" /> 
                                    <rect x="52" y="215" rx="6" ry="6" width="132" height="15" /> 
                                    <rect x="52" y="246" rx="6" ry="6" width="132" height="15" />
                                </clipPath>
                                <linearGradient id="fill">
                                    <stop
                                        offset="0.599964"
                                        stop-color="#d9d9d9"
                                        stop-opacity="1"
                                    >
                                        <animate
                                        attributeName="offset"
                                        values="-2; -2; 1"
                                        keyTimes="0; 0.25; 1"
                                        dur="2s"
                                        repeatCount="indefinite"
                                        ></animate>
                                    </stop>
                                    <stop
                                        offset="1.59996"
                                        stop-color="#ecebeb"
                                        stop-opacity="1"
                                    >
                                        <animate
                                        attributeName="offset"
                                        values="-1; -1; 2"
                                        keyTimes="0; 0.25; 1"
                                        dur="2s"
                                        repeatCount="indefinite"
                                        ></animate>
                                    </stop>
                                    <stop
                                        offset="2.59996"
                                        stop-color="#d9d9d9"
                                        stop-opacity="1"
                                    >
                                        <animate
                                        attributeName="offset"
                                        values="0; 0; 3"
                                        keyTimes="0; 0.25; 1"
                                        dur="2s"
                                        repeatCount="indefinite"
                                        ></animate>
                                    </stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </a>
                </div>
                <div class="performance-grid" v-else>
                    <div class="item" v-for="tourist in filteredTourists" :key="tourist.id">
                        <img :src="tourist.profile_picture || '/user.jpg'" alt="">
                        <h2>{{ tourist.full_name }}</h2>
                        <div class="tourist-details">
                            <div class="tags">
                                <span class="tag">{{ tourist.nationality }}</span>
                                <span class="tag">{{ tourist.gender }}</span>
                            </div>
                            <p class="email"><i class="material-icons">email</i> {{ tourist.email }}</p>
                            <p class="address"><i class="material-icons">location_on</i> {{ tourist.address }}</p>
                            
                            <!-- Hobbies/Interests Section -->
                            <div class="interests-section">
                                <h3>Interests</h3>
                                <div class="hobby-tags">
                                    <span v-for="(hobby, index) in parseHobbies(tourist.hobbies)" 
                                          :key="index" 
                                          class="hobby-tag">
                                        {{ hobby.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Accommodation Section -->
                            <div class="accommodation-section">
                                <h3>Accommodation</h3>
                                <p><strong>Name:</strong> {{ tourist.accommodation_name }}</p>
                                <p><strong>Location:</strong> {{ tourist.accommodation_location }}</p>
                                <p><strong>Duration:</strong> {{ tourist.accommodation_days }} days</p>
                            </div>
                        </div>
                        <div class="actions">
                            <router-link 
                                v-if="tourist.id" 
                                :to="{ 
                                    name: 'admin/tourist-detail', 
                                    params: { id: tourist.id } 
                                }" 
                                class="view-btn"
                            >
                                <i class="material-icons">visibility</i>
                            </router-link>
                            <RouterLink 
                                v-if="tourist.id" 
                                :to="{ 
                                    name: 'admin/tourist-edit', 
                                    params: { id: tourist.id } 
                                }" 
                                class="edit-btn"
                            >
                                <i class="material-icons">edit</i>
                            </RouterLink>
                            <button 
                                v-if="tourist.id" 
                                @click="deleteTourist(tourist.id)" 
                                class="delete-btn"
                            >
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AdminSidenav from '@/components/AdminSidenav.vue'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const tourists = ref('loading...')
const searchQuery = ref('')

const filteredTourists = computed(() => {
    if (tourists.value === 'loading...') return []
    if (!searchQuery.value) return tourists.value

    const query = searchQuery.value.toLowerCase()
    return tourists.value.filter(tourist => 
        tourist.full_name.toLowerCase().includes(query) ||
        tourist.email.toLowerCase().includes(query) ||
        tourist.nationality.toLowerCase().includes(query) ||
        tourist.address.toLowerCase().includes(query) ||
        tourist.accommodation_name?.toLowerCase().includes(query) ||
        tourist.accommodation_location?.toLowerCase().includes(query)
    )
})

const parseHobbies = (hobbies) => {
    try {
        return typeof hobbies === 'string' ? JSON.parse(hobbies) : hobbies || []
    } catch {
        return []
    }
}

const fetchTourists = async () => {
    try {
        const response = await makeRequest.get('/admin/tourists/list')
        console.log('Full response:', response)
        
        // Robust data extraction
        let touristData = null

        // Check multiple possible response structures
        if (response.data && response.data.success && response.data.data) {
            touristData = response.data.data
        } else if (response.data && Array.isArray(response.data)) {
            touristData = response.data
        } else if (response.data) {
            touristData = response.data
        } else {
            touristData = response
        }

        // Ensure touristData is an array
        if (!Array.isArray(touristData)) {
            touristData = Object.values(touristData)
        }

        // Normalize tourist data with strict id check
        tourists.value = touristData
            .filter(tourist => tourist && tourist.id) // Ensure id exists
            .map(tourist => ({
                id: tourist.id, // Ensure id is always present
                full_name: tourist.full_name || tourist.name || 'Unknown',
                email: tourist.email || 'N/A',
                nationality: tourist.nationality || 'N/A',
                gender: tourist.gender || 'N/A',
                address: tourist.address || 'N/A',
                hobbies: tourist.hobbies || [],
                profile_picture: tourist.profile_picture || '/user.jpg',
                accommodation_name: tourist.accommodation_name || 'N/A',
                accommodation_location: tourist.accommodation_location || 'N/A',
                accommodation_days: tourist.accommodation_days || 0
            }))
    } catch (error) {
        console.error('Error fetching tourists:', error)
        toast.error('Failed to load tourist information')
        tourists.value = []
    }
}

const deleteTourist = async (id) => {
    if (!confirm('Are you sure you want to delete this tourist?')) {
        return
    }

    try {
        const response = await makeRequest.delete(`/admin/tourists/${id}`)
        if (response.data.success) {
            toast.fire({
                title: 'Tourist deleted successfully',
                icon: 'success'
            })
            await fetchTourists()
        } else {
            throw new Error(response.data.message || 'Failed to delete tourist')
        }
    } catch (error) {
        console.error('Error deleting tourist:', error)
        toast.fire({
            title: error.response?.data?.message || 'Failed to delete tourist',
            text: 'Please try again later',
            icon: 'error'
        })
    }
}

onMounted(fetchTourists)
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
    align-items: center;

    .search-container {
        width: 70%;
        padding: 15px;

        .input-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: auto;

            input {
                width: 100%;
                display: block;
                border: 2px solid transparent;
                padding: 15px 30px;
                padding-left: 60px;
                font-size: 16px;
                background-color: rgba(0, 0, 0, 0.019);
                border-radius: 6px;
                outline: 0;
                transition: all .2s cubic-bezier(0.165, 0.84, 0.44, 1);

                &:hover,
                &:active,
                &:focus {
                    background-color: rgba(0, 0, 0, 0.056);
                }

                &:focus {
                    border-bottom: 2px solid #2A9DF2;
                    border-bottom-left-radius: 0;
                    border-bottom-right-radius: 0;
                }
            }

            i.material-icons {
                position: absolute;
                left: 0;
                top: 0;
                font-size: 30px;
                padding: 12px;
                color: #131010;
            }
        }
    }

    .profile-container {
        width: 30%;
        text-align: right;
        padding: 15px 30px;

        a {
            color: #131010;
            text-decoration: none;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 7px;
            transition: all .1s linear;

            &:hover {
                background-color: rgba(0, 0, 0, 0.056);
            }

            &:active {
                background-color: rgba(0, 0, 0, 0.105);
            }

            img {
                width: 100%;
                max-width: 50px;
                vertical-align: middle;
                border-radius: 50%;
                margin-left: 20px;
            }
        }
    }
}

.page-container {
    padding: 30px;
    width: 100%;
    max-width: 1400px;
    margin: auto;

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;

        .titles {
            h1 {
                font-size: 25px;
                margin-top: 30px;
            }
            
            p {
                color: #939393;
                margin-bottom: 0;
            }
        }

        .actions {
            .add-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #2A9DF2;
                color: white;
                padding: 10px 20px;
                border-radius: 4px;
                text-decoration: none;
                transition: all 0.2s;

                &:hover {
                    background: darken(#2A9DF2, 10%);
                }

                i {
                    font-size: 20px;
                }
            }
        }
    }

    .performance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;

        .item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;

            &:hover {
                transform: translateY(-5px);
            }

            img {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                object-fit: cover;
                margin: 0 auto 15px;
                display: block;
            }

            h2 {
                text-align: center;
                margin-bottom: 10px;
                color: #333;
            }
        }
    }
}

.tourist-details {
    padding: 15px 0;
    
    .tags {
        margin-bottom: 10px;
        
        .tag {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 0.9em;
        }
    }

    p {
        display: flex;
        align-items: center;
        margin: 5px 0;
        
        i.material-icons {
            margin-right: 5px;
            font-size: 18px;
            color: #666;
        }
    }
}

.interests-section, .accommodation-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;

    h3 {
        font-size: 1em;
        color: #666;
        margin-bottom: 10px;
    }
}

.hobby-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;

    .hobby-tag {
        background: #158ADF;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }
}

.accommodation-section {
    p {
        margin: 5px 0;
        font-size: 0.9em;
        
        strong {
            color: #666;
        }
    }
}

.actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;

    button, a {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        border-radius: 5px;
        transition: background 0.2s;

        i {
            font-size: 20px;
        }

        &.view-btn i { color: #158ADF; }
        &.edit-btn i { color: #28a745; }
        &.delete-btn i { color: #dc3545; }

        &:hover {
            background: rgba(0,0,0,0.05);
        }
    }
}
</style>