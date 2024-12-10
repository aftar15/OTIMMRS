<template>
    <div class="container">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
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
                        <h1>Add New Attraction</h1>
                        <p>Create a new tourist attraction</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/attractions" class="button secondary">
                            <i class="material-icons">arrow_back</i>
                            Back to Attractions
                        </router-link>
                    </div>
                </div>
                
                <form @submit.prevent="handleSubmit" class="form-container">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Name *</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                required
                                placeholder="Enter attraction name"
                            >
                            <span class="error" v-if="errors.name">{{ errors.name[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Category *</label>
                            <select v-model="form.category" required>
                                <option value="">Select category</option>
                                <option value="Natural">Natural</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Historical">Historical</option>
                                <option value="Adventure">Adventure</option>
                                <option value="Entertainment">Entertainment</option>
                            </select>
                            <span class="error" v-if="errors.category">{{ errors.category[0] }}</span>
                        </div>

                        <div class="form-group full-width">
                            <label>Description *</label>
                            <textarea 
                                v-model="form.description" 
                                required
                                rows="4"
                                placeholder="Enter attraction description"
                            ></textarea>
                            <span class="error" v-if="errors.description">{{ errors.description[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Location *</label>
                            <input 
                                type="text" 
                                v-model="form.location" 
                                required
                                placeholder="Enter location"
                            >
                            <span class="error" v-if="errors.location">{{ errors.location[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Opening Hours *</label>
                            <input 
                                type="text" 
                                v-model="form.opening_hours" 
                                required
                                placeholder="e.g., 9:00 AM - 5:00 PM"
                            >
                            <span class="error" v-if="errors.opening_hours">{{ errors.opening_hours[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Admission Fee *</label>
                            <input 
                                type="number" 
                                v-model="form.admission_fee" 
                                required
                                min="0"
                                step="0.01"
                                placeholder="Enter admission fee"
                            >
                            <span class="error" v-if="errors.admission_fee">{{ errors.admission_fee[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Latitude *</label>
                            <input 
                                type="number" 
                                v-model="form.latitude" 
                                required
                                step="any"
                                placeholder="Enter latitude"
                            >
                            <span class="error" v-if="errors.latitude">{{ errors.latitude[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Longitude *</label>
                            <input 
                                type="number" 
                                v-model="form.longitude" 
                                required
                                step="any"
                                placeholder="Enter longitude"
                            >
                            <span class="error" v-if="errors.longitude">{{ errors.longitude[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input 
                                type="tel" 
                                v-model="form.contact_phone"
                                placeholder="Enter contact phone"
                            >
                            <span class="error" v-if="errors.contact_phone">{{ errors.contact_phone[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Contact Email</label>
                            <input 
                                type="email" 
                                v-model="form.contact_email"
                                placeholder="Enter contact email"
                            >
                            <span class="error" v-if="errors.contact_email">{{ errors.contact_email[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>Website</label>
                            <input 
                                type="url" 
                                v-model="form.website"
                                placeholder="Enter website URL"
                            >
                            <span class="error" v-if="errors.website">{{ errors.website[0] }}</span>
                        </div>

                        <div class="form-group full-width">
                            <label>Image *</label>
                            <div class="image-upload">
                                <input 
                                    type="file" 
                                    ref="imageInput"
                                    @change="handleImageChange"
                                    accept="image/*"
                                    required
                                >
                                <div v-if="imagePreview" class="image-preview">
                                    <img :src="imagePreview" alt="Preview">
                                </div>
                            </div>
                            <span class="error" v-if="errors.image">{{ errors.image[0] }}</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" @click="$router.push('/admin/attractions')" class="button secondary">
                            Cancel
                        </button>
                        <button type="submit" class="button primary" :disabled="loading">
                            <span v-if="loading">Creating...</span>
                            <span v-else>Create Attraction</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../../stores/auth';
import AdminSidenav from '../../../components/AdminSidenav.vue';
import AttractionService from '../../../services/AttractionService';
import { useToast } from 'vue-toastification';

const router = useRouter();
const auth = useAuthStore();
const toast = useToast();
const loading = ref(false);
const imagePreview = ref(null);
const errors = ref({});

const form = reactive({
    name: '',
    description: '',
    location: '',
    category: '',
    latitude: '',
    longitude: '',
    opening_hours: '',
    admission_fee: '',
    contact_phone: '',
    contact_email: '',
    website: '',
    image: null
});

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleSubmit = async () => {
    try {
        loading.value = true;
        errors.value = {};

        const formData = new FormData();
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                formData.append(key, form[key]);
            }
        });

        await AttractionService.createAttraction(formData);
        toast.success('Attraction created successfully');
        router.push('/admin/attractions');
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            toast.error('Failed to create attraction');
        }
        console.error(error);
    } finally {
        loading.value = false;
    }
};
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
    justify-content: flex-end;
}

.page-container {
    padding: 2rem;
    max-width: 1200px;
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
}

.button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    
    &.primary {
        background: #0066cc;
        color: white;
        
        &:hover {
            background: #0052a3;
        }
        
        &:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    }
    
    &.secondary {
        background: #f5f5f5;
        color: #333;
        
        &:hover {
            background: #eee;
        }
    }
    
    i {
        font-size: 1.2rem;
    }
}

.form-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-group {
    &.full-width {
        grid-column: 1 / -1;
    }
    
    label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 500;
    }
    
    input, select, textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        
        &:focus {
            outline: none;
            border-color: #0066cc;
        }
    }
    
    textarea {
        resize: vertical;
    }
    
    .error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
}

.image-upload {
    input[type="file"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        
        &::-webkit-file-upload-button {
            background: #f5f5f5;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-right: 1rem;
            cursor: pointer;
            
            &:hover {
                background: #eee;
            }
        }
    }
    
    .image-preview {
        margin-top: 1rem;
        
        img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 6px;
            object-fit: cover;
        }
    }
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>