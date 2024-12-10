<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                        <input type="text" v-model="searchQuery" placeholder="Search attractions...">
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
                        <h1>Tourist Attractions</h1>
                        <p>{{ attractions?.length || 0 }} attraction(s)</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/attractions/add" class="button">
                            <i class="material-icons">add</i>
                            Add Attraction
                        </router-link>
                    </div>
                </div>
                <div class="performance-grid" v-if="loading">
                    <div class="item-skeleton" v-for="i in 4" :key="i">
                        <div class="image-skeleton"></div>
                    </div>
                </div>
                <div class="performance-grid" v-else>
                    <div class="item" v-for="attraction in filteredAttractions" :key="attraction.id">
                        <div class="image-container">
                            <img :src="getStorageUrl(attraction.image_url)" :alt="attraction.name" class="attraction-image">
                            <div class="informations">
                                <div class="texts">
                                    <p class="tag"><i>#{{ attraction.category || 'Unknown' }}</i></p>
                                    <h1>{{ attraction.name || 'Tourist Attraction' }}</h1>
                                    <p class="description">{{ attraction.description || 'No description available' }}</p>
                                    <StarRatings :item="attraction" />
                                </div>
                            </div>
                            <div class="actions">
                                <router-link :to="`/admin/attractions/${attraction.id}`" class="edit-button">
                                    <i class="material-icons">edit</i>
                                </router-link>
                                <button @click="confirmDelete(attraction)" class="delete-button">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </div>
                    </div>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal" v-if="showDeleteModal">
            <div class="modal-content">
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete "{{ selectedAttraction?.name }}"?</p>
                <div class="modal-actions">
                    <button @click="showDeleteModal = false" class="cancel-btn">Cancel</button>
                    <button @click="deleteAttraction" class="delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import AdminSidenav from '../../components/AdminSidenav.vue';
import StarRatings from '../../components/StarRatings.vue';
import AttractionService from '../../services/AttractionService';
import { useToast } from 'vue-toastification';

const auth = useAuthStore();
const toast = useToast();
const attractions = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);
const showDeleteModal = ref(false);
const selectedAttraction = ref(null);
const searchQuery = ref('');

const filteredAttractions = computed(() => {
    if (!attractions.value) return [];
    return attractions.value;
});


// Add debounce function
let searchTimeout;

// Watch for changes in search query
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchAttractions();
    }, 500);
});

const getStorageUrl = (path) => {
    return path ? `${import.meta.env.VITE_STORAGE_URL}/${path}` : '/placeholder.jpg';
};

const fetchAttractions = async (page = 1) => {
    try {
        loading.value = true;
        const response = await AttractionService.getAttractions(page, { 
            search: searchQuery.value,
            items_per_page: 12 
        });
        
        if (response?.data?.success) {
            attractions.value = response.data.data.data || [];
            totalPages.value = Math.ceil(response.data.data.total / response.data.data.per_page);
            currentPage.value = response.data.data.current_page;
        } else {
            console.error('Invalid response structure:', response);
            attractions.value = [];
            totalPages.value = 1;
            currentPage.value = 1;
        }
    } catch (error) {
        console.error('Error fetching attractions:', error);
        toast.error('Failed to fetch attractions');
        attractions.value = [];
        totalPages.value = 1;
        currentPage.value = 1;
    } finally {
        loading.value = false;
    }
};




const confirmDelete = (attraction) => {
    selectedAttraction.value = attraction;
    showDeleteModal.value = true;
};

const deleteAttraction = async () => {
    try {
        await AttractionService.deleteAttraction(selectedAttraction.value.id);
        toast.success('Attraction deleted successfully');
        await fetchAttractions(currentPage.value);
    } catch (error) {
        toast.error('Failed to delete attraction');
        console.error(error);
    } finally {
        showDeleteModal.value = false;
        selectedAttraction.value = null;
    }
};

const changePage = async (page) => {
    if (page < 1 || page > totalPages.value) return;
    await fetchAttractions(page);
};

onMounted(async () => {
    await fetchAttractions();
});
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
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        
        &:hover {
            transform: translateY(-4px);
        }

        .attraction-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                h1 {
                    margin: 0.5rem 0;
                    font-size: 1.5rem;
                    font-weight: 600;
                }
                
                .tag {
                    margin: 0;
                    font-size: 0.875rem;
                    opacity: 0.9;
                }
                
                .description {
                    margin: 0.5rem 0;
                    font-size: 0.875rem;
                    opacity: 0.9;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
            }
        }
        
        .actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        &:hover .actions {
            opacity: 1;
        }
        
        .edit-button, .delete-button {
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            
            &:hover {
                transform: scale(1.1);
            }
            
            i {
                font-size: 20px;
                color: #333;
            }
        }
        
        .delete-button {
            i {
                color: #dc3545;
            }
        }
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

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    width: 100%;
    max-width: 400px;
    
    h2 {
        margin: 0 0 1rem;
    }
    
    p {
        margin: 0 0 1.5rem;
        color: #666;
    }
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    
    button {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        
        &.cancel-btn {
            background: #f5f5f5;
            color: #333;
            
            &:hover {
                background: #eee;
            }
        }
        
        &.delete-btn {
            background: #dc3545;
            color: white;
            
            &:hover {
                background: #c82333;
            }
        }
    }
}

.item-skeleton {
    .image-skeleton {
        padding-bottom: 66.67%;
        background: #f5f5f5;
        border-radius: 12px;
        animation: pulse 1.5s infinite;
    }
}

@keyframes pulse {
    0% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
    100% {
        opacity: 0.6;
    }
}
</style>