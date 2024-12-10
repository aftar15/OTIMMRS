<!-- Comments.vue -->
<template>
  <div class="container">
    <AdminSidenav></AdminSidenav>
    <div class="login-container">
      <div class="navbar">
        <div class="search-container">
          <div class="input-container">
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search comments..."
            >
            <i class="material-icons">search</i>
          </div>
        </div>
        <div class="profile-container">
          <a href="#!">
            Hello, {{ auth.admin.name }}!
            <img :src="auth.admin.profile_picture || '/user.jpg'" alt="">
          </a>
        </div>
      </div>

      <div class="page-container">
        <div class="header">
          <div class="titles">
            <h1>
              <i class="material-icons">forum</i>
              Tourist Comments
            </h1>
            <p>{{ filteredComments.length }} comment(s)</p>
          </div>
          <div class="filters">
            <select v-model="selectedType" class="filter-select">
              <option value="all">All Types</option>
              <option value="attraction">Attractions</option>
              <option value="accommodation">Accommodations</option>
              <option value="activity">Activities</option>
            </select>

            <input 
              type="date" 
              v-model="dateFilter"
              class="filter-date"
            />

            <button @click="resetFilters" class="reset-btn">
              <i class="material-icons">refresh</i>
              Reset Filters
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div class="performance-grid" v-if="isLoading">
          <div class="item-skeleton" v-for="i in 4" :key="i">
            <div class="image-skeleton"></div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          {{ error }}
          <button @click="fetchComments" class="retry-btn">
            <i class="material-icons">refresh</i>
            Retry
          </button>
        </div>

        <!-- Comments Grid -->
        <div v-else class="performance-grid">
          <div v-for="comment in filteredComments" :key="comment.id" class="comment-card">
            <div class="comment-header">
              <span class="comment-type" :class="comment.type">
                {{ comment.type?.charAt(0).toUpperCase() + comment.type?.slice(1) }}
              </span>
              <div class="comment-actions">
                <button @click="deleteComment(comment.id)" class="delete-btn">
                  <i class="material-icons">delete</i>
                </button>
              </div>
            </div>

            <div class="comment-content">
              <h3 class="destination-name">{{ comment.destination_name }}</h3>
              <div class="comment-details">
                <div class="detail-item">
                  <i class="material-icons">directions_car</i>
                  <div>
                    <strong>Transportation:</strong>
                    <p>{{ comment.transportation || 'N/A' }}</p>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="material-icons">payments</i>
                  <div>
                    <strong>Transportation Fee:</strong>
                    <p>₱{{ comment.transportation_fee || 'N/A' }}</p>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="material-icons">room_service</i>
                  <div>
                    <strong>Services:</strong>
                    <p>{{ comment.services || 'N/A' }}</p>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="material-icons">warning</i>
                  <div>
                    <strong>Road Problems:</strong>
                    <p>{{ comment.road_problems || 'N/A' }}</p>
                  </div>
                </div>
                <div class="detail-item">
                  <i class="material-icons">trending_up</i>
                  <div>
                    <strong>Price Increase:</strong>
                    <p>{{ comment.price_increase || 'N/A' }}</p>
                  </div>
                </div>
                <div v-if="comment.others" class="detail-item">
                  <i class="material-icons">notes</i>
                  <div>
                    <strong>Additional Comments:</strong>
                    <p>{{ comment.others }}</p>
                  </div>
                </div>
              </div>

              <div class="tourist-info">
                <i class="material-icons">person</i>
                <div>
                  <strong>Tourist:</strong>
                  <p>{{ comment.tourist_name || 'Anonymous' }}</p>
                </div>
                <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- No Comments Message -->
          <div v-if="!isLoading && filteredComments.length === 0" class="no-comments">
            <i class="material-icons">forum</i>
            <p>No comments found.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="!isLoading && !error && filteredComments.length > 0" class="pagination">
          <button 
            :disabled="currentPage === 1" 
            @click="changePage(-1)"
            class="page-btn"
          >
            Previous
          </button>
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
          <button 
            :disabled="currentPage === totalPages" 
            @click="changePage(1)"
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
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import AdminSidenav from '@/components/AdminSidenav.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const auth = useAuthStore();
const comments = ref([]);
const currentPage = ref(1);
const selectedType = ref('all');
const dateFilter = ref('');

const totalPages = ref(1);
const itemsPerPage = 10;
const isLoading = ref(false);
const error = ref(null);
const searchQuery = ref('');

const fetchComments = async () => {
  isLoading.value = true;
  error.value = null;
  
  try {
    const response = await api.get('/admin/comments', {
      params: {
        page: currentPage.value,
        type: selectedType.value !== 'all' ? selectedType.value : undefined,
        date: dateFilter.value || undefined,
        search: searchQuery.value || undefined,
        per_page: itemsPerPage
      }
    });

    if (response.data.status === 'success') {
      comments.value = response.data.data || [];
      totalPages.value = response.data.last_page || 1;
    } else {
      throw new Error(response.data.message || 'Failed to fetch comments');
    }
  } catch (error) {
    console.error('Error fetching comments:', error);
    error.value = 'Failed to load comments. Please try again.';
    comments.value = [];
    toast.error('Failed to load comments');
  } finally {
    isLoading.value = false;
  }
};

const deleteComment = async (id) => {
  if (!confirm('Are you sure you want to delete this comment?')) {
    return;
  }

  try {
    const response = await api.delete(`/admin/comments/${id}`);
    if (response.data.status === 'success') {
      toast.success('Comment deleted successfully');
      await fetchComments();
    } else {
      throw new Error(response.data.message || 'Failed to delete comment');
    }
  } catch (error) {
    console.error('Error deleting comment:', error);
    toast.error('Failed to delete comment');
  }
};

const resetFilters = () => {
  selectedType.value = 'all';
  dateFilter.value = '';
  searchQuery.value = '';
  currentPage.value = 1;
  fetchComments();
};

const changePage = (delta) => {
  currentPage.value += delta;
  fetchComments();
};

const filteredComments = computed(() => {
  return comments.value || [];
});

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  fetchComments();
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

  .search-container {
    width: 70%;
    padding: 15px;

    .input-container {
      position: relative;
      width: 100%;
      max-width: 800px;

      input {
        width: 100%;
        padding: 12px 20px 12px 50px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;

        &:focus {
          outline: none;
          border-color: #2A9DF2;
        }
      }

      i.material-icons {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        padding: 0 15px;
        color: #666;
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
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      border-radius: 7px;
      transition: all .1s linear;

      &:hover {
        background-color: rgba(0, 0, 0, 0.056);
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
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
  
  .titles {
    h1 {
      margin: 0;
      font-size: 2rem;
      color: #333;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        font-size: 2rem;
        color: #2A9DF2;
      }
    }
    
    p {
      margin: 0.5rem 0 0;
      color: #666;
    }
  }
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;

  .filter-select,
  .filter-date {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background: white;

    &:focus {
      outline: none;
      border-color: #2A9DF2;
    }
  }

  .reset-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f0f0f0;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #666;

    &:hover {
      background: #e0e0e0;
    }

    i {
      font-size: 18px;
    }
  }
}

.performance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.comment-card {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;

  &:hover {
    transform: translateY(-5px);
  }
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-bottom: 1px solid #eee;

  .comment-actions {
    display: flex;
    gap: 0.5rem;

    .delete-btn {
      background: none;
      border: none;
      padding: 0.5rem;
      cursor: pointer;
      border-radius: 50%;
      transition: all 0.2s;
      color: #dc3545;

      &:hover {
        background-color: rgba(220, 53, 69, 0.1);
      }

      i {
        font-size: 1.25rem;
      }
    }
  }
}

.comment-type {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;

  &.attraction {
    background-color: #e3f2fd;
    color: #1976d2;
  }

  &.accommodation {
    background-color: #f3e5f5;
    color: #7b1fa2;
  }

  &.activity {
    background-color: #e8f5e9;
    color: #388e3c;
  }
}

.comment-date {
  font-size: 0.875rem;
  color: #666;
}

.comment-content {
  padding: 1rem;
}

.destination-name {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  color: #333;
}

.comment-details {
  p {
    margin: 0.5rem 0;
    font-size: 0.875rem;
    color: #666;

    strong {
      color: #333;
    }
  }
}

.tourist-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;

  i {
    color: #2A9DF2;
    font-size: 1.25rem;
  }

  div {
    flex: 1;

    strong {
      display: block;
      color: #333;
      margin-bottom: 0.25rem;
    }

    p {
      margin: 0;
      color: #666;
      font-size: 0.875rem;
    }
  }

  .comment-date {
    color: #666;
    font-size: 0.75rem;
  }
}

.item-skeleton {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  height: 300px;
  animation: pulse 1.5s infinite;
}

.error-state {
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: 10px;
  color: #dc3545;

  .retry-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      background: #c82333;
    }

    i {
      font-size: 18px;
    }
  }
}

.no-comments {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 10px;
  color: #666;

  i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 1rem;
  }

  p {
    margin: 0;
    font-size: 1.125rem;
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

.detail-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1rem;

  i {
    color: #2A9DF2;
    font-size: 1.25rem;
  }

  div {
    flex: 1;

    strong {
      display: block;
      color: #333;
      margin-bottom: 0.25rem;
    }

    p {
      margin: 0;
      color: #666;
      font-size: 0.875rem;
    }
  }
}
</style>