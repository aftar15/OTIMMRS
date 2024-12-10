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
                            <a href="/admin/accommodations" class="icon-button">
                                <i class="material-icons">
                                    arrow_back
                                </i>
                            </a>
                            {{ isLoading ? 'Loading...' : name }}
                        </h1>
                    </div>
                    <div class="action">
                        <button class="icon-button" @click="deleteAccommodation()" title="Delete Accommodation">
                            <i class="material-icons">delete</i>
                        </button>
                        <button @click="saveAccommodation()" class="button">
                            <i class="material-icons">add</i>
                            Save Changes
                        </button>
                    </div>
                    
                </div>
                <div class="skeleton" style="text-align:center" v-if="isLoading">
                    <svg
                        role="img"
                        width="1114"
                        height="286"
                        aria-labelledby="loading-aria"
                        viewBox="0 0 1114 286"
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
                                <rect x="0" y="79" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="0" y="158" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="0" y="0" rx="9" ry="9" width="1114" height="47" /> 
                                <rect x="380" y="79" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="380" y="158" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="760" y="79" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="760" y="158" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" /> 
                                <rect x="0" y="237" rx="9" ry="9" width="354" height="47" /> 
                                <path d="M 0 -0.5 h 354.001" />
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
                </div>
                <div v-else>
                    
                    <h2 class="subheader">Accommodation Information</h2>
                    
                    <!-- Image Upload -->
                    <div class="input-group">
                        <div class="image-upload-container">
                            <div class="preview-container">
                                <img :src="imagePreview || image_url || '/placeholder.jpg'" 
                                     alt="Accommodation preview" 
                                     class="preview-image">
                                <input type="file" 
                                       @change="handleImageUpload" 
                                       accept="image/*"
                                       class="file-input">
                            </div>
                            <div class="upload-instructions">
                                Click to upload or drag and drop<br>
                                PNG, JPG, GIF up to 2MB
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-container one-third">
                            <input v-model="name" type="text" placeholder="Accommodation Name">
                            <i class="material-icons">business</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="description" type="text" placeholder="Description">
                            <i class="material-icons">message</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="address" type="text" placeholder="Address">
                            <i class="material-icons">location_on</i>
                        </div>
                        <div class="input-container one-third">
                            <select v-model="accommodation_type">
                                <option value="" disabled>-- Select Type --</option>
                                <option value="hotel">Hotel</option>
                                <option value="resort">Resort</option>
                                <option value="homestay">Homestay</option>
                                <option value="apartment">Apartment</option>
                            </select>
                            <i class="material-icons">category</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="check_in_time" type="time" placeholder="Check-in Time">
                            <i class="material-icons">access_time</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="check_out_time" type="time" placeholder="Check-out Time">
                            <i class="material-icons">access_time</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="price_range" type="text" placeholder="Price Range">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="contact_number" type="text" placeholder="Contact Number">
                            <i class="material-icons">phone</i>
                        </div>
                        <div class="input-container full-width">
                            <textarea v-model="amenities" placeholder="Amenities (separated by commas)"></textarea>
                            <i class="material-icons">hotel</i>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="subheader">{{ comments.length }} Comment(s)</h2>
                    <ul>
                        <li v-for="comment of comments">
                            <strong>{{ comment.tourist.full_name }}</strong>
                            <ul>
                                <li>Transportation: {{ comment.transportation }}</li>
                                <li>Transportation Fee: {{ comment.transportation_fee }}</li>
                                <li>Services: {{ comment.services }}</li>
                                <li>Road Problems: {{ comment.road_problems }}</li>
                                <li>Price Increase: {{ comment.price_increase }}</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

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
            // background-color: #2A9DF2;
            color: #c73b3b;
            padding: 13px;
            border-radius: 50%;
            // box-shadow: 0 3px 6px rgba(0, 0, 0, 0.082);
            text-decoration: none;
            transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 0;
            margin: 0 10px;
            cursor: pointer;

            &:hover {
                background-color: rgba(0, 0, 0, 0.114);
            }

            &:active {
                transform: scale(0.9);
            }
        }
    }
}

.login-container {
    padding-left: 350px;

    .navbar {
        display: flex;
        align-items: center;

        .search-container {
            width: 70%;
            // outline: 1px solid red;
            padding: 15px;

            .input-container {
                position: relative;
                width: 100%;
                max-width: 800px;

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
                    // outline: 1px solid red;
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
                    margin-left: 20px;;
                }
            }

        }
    }

    .page-container {
        padding: 30px;
        width:100%;
        max-width: 1400px;
        margin: auto;

        h1 {
            font-size: 25px;
            margin-top: 30px;

            .icon-button {
                padding: 5px 10px;
                display: inline;
                // outline: 1px solid red;
                // background-color: rgb(227, 227, 227);
                border-radius: 50%;
                transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);

                &:hover {
                    background-color: rgb(227, 227, 227);
                }
                &:focus {
                    background-color: #2A9DF2;
                    color: white;
                    transform: scale(0.9);
                }
            }
        }

        .subheader {
            font-size: 20px;
            color: #686868;
            margin: 25px 0;
            margin-top: 50px;
            font-weight: normal;
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;

            .input-container {
            position: relative;
            // width: calc(100% - 15px);
            // margin: 15px;
            width: calc(100% - 30px);

            &.one-third {
                width: calc(33.33% - 30px);
            }

            input,
            select {
                width: 100%;
                padding: 15px 20px;
                padding-right: 50px;
                font-size: 16px;
                border-top-right-radius: 15px;
                border-top-left-radius: 15px;
                outline: 0;
                border: 0;
                border-bottom: 2px solid #2A9DF2;
                background-color: #e7e8ee;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;

                &:focus {
                box-shadow: 0 3px 6px #2a9bf253;
                }

                &[type="date"]::-webkit-inner-spin-button,
                &[type="date"]::-webkit-calendar-picker-indicator {
                    opacity: 0;
                    // margin-right: -25px;
                    padding: 5px;
                    opacity: 0;
                    // outline: 1px solid red;
                }

                option {
                border: 0;
                }
            }

            i.material-icons {
                color: #A1A1A1;
                position: absolute;
                right: 0;
                top: 0;
                // outline: 1px solid red;
                padding: 16px;
                margin-right: 5px;
                pointer-events: none;
            }
            }
        }
    }
}

.image-upload-container {
    width: 100%;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;

    .preview-container {
        width: 200px;
        height: 200px;
        position: relative;
        border: 2px dashed #ccc;
        border-radius: 8px;
        overflow: hidden;
        
        &:hover {
            border-color: #666;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
    }

    .upload-instructions {
        color: #666;
        font-size: 14px;
        line-height: 1.4;
    }
}

.full-width {
    width: 100% !important;
    
    textarea {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        resize: vertical;
    }
}
</style>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { makeRequest } from '@/plugins/axios'
import AdminSidenav from '@/components/AdminSidenav.vue'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const isLoading = ref(true)

// Form data
const name = ref('')
const description = ref('')
const address = ref('')
const accommodation_type = ref('')
const price_range = ref('')
const amenities = ref('')
const check_in_time = ref('')
const check_out_time = ref('')
const contact_number = ref('')
const image_url = ref('')
const comments = ref([])

// Image upload
const selectedImage = ref(null)
const imagePreview = ref('')

async function handleImageUpload(event) {
    const file = event.target.files[0]
    if (!file) return

    // File type validation
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
        toast.error('Please select a valid image file (JPEG, PNG, JPG, GIF)')
        return
    }

    // File size validation (2MB)
    if (file.size > 2 * 1024 * 1024) {
        toast.error('Image size should not exceed 2MB')
        return
    }

    selectedImage.value = file
    imagePreview.value = URL.createObjectURL(file)

    // Upload image immediately
    const formData = new FormData()
    formData.append('image', file)

    try {
        const response = await makeRequest.post(`/accommodations/${route.params.id}/image`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        if (response.data.status === 'success') {
            image_url.value = response.data.data.image_url
            toast.success('Image uploaded successfully')
        }
    } catch (error) {
        console.error('Error uploading image:', error)
        toast.error('Failed to upload image')
    }
}

async function saveAccommodation() {
    try {
        // Convert comma-separated amenities string to array
        const amenitiesArray = amenities.value
            ? amenities.value.split(',').map(item => item.trim()).filter(item => item)
            : [];

        const data = {
            name: name.value || '',
            description: description.value || '',
            location: address.value || '',
            category: accommodation_type.value || '',
            price_per_night: parseFloat(price_range.value) || 0,
            amenities: amenitiesArray,
            contact_info: JSON.stringify({
                phone: contact_number.value || '',
                email: '',
                website: ''
            }),
            check_in_time: check_in_time.value || '',
            check_out_time: check_out_time.value || '',
            image_url: image_url.value || null
        };

        console.log('Sending data:', data);

        const response = await makeRequest.put(`/admin/accommodations/${route.params.id}`, data, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.data.success) {
            toast.success('Accommodation updated successfully');
            router.push('/admin/accommodations');
        } else {
            console.error('Update failed:', response.data);
            toast.error(response.data.message || 'Failed to update accommodation');
        }
    } catch (error) {
        console.error('Error updating accommodation:', error);
        console.error('Error details:', error.response?.data);
        toast.error(error.response?.data?.message || 'Failed to update accommodation');
    }
}

async function deleteAccommodation() {
    if (!confirm('Are you sure you want to delete this accommodation?')) return

    try {
        const response = await makeRequest.delete(`/accommodations/${route.params.id}`)
        
        if (response.data.success) {
            toast.success('Accommodation deleted successfully')
            router.push('/admin/accommodations')
        }
    } catch (error) {
        console.error('Error deleting accommodation:', error)
        toast.error('Failed to delete accommodation')
    }
}

onMounted(async () => {
    try {
        const id = route.params.id;
        
        // Fetch accommodation details
        const response = await makeRequest.get(`/admin/accommodations/${id}`);
        if (response.data.success) {
            const data = response.data.data;
            name.value = data.name;
            description.value = data.description;
            address.value = data.location;
            accommodation_type.value = data.category;
            price_range.value = data.price_per_night;
            // Convert amenities array to comma-separated string for display
            amenities.value = Array.isArray(data.amenities) ? data.amenities.join(', ') : '';
            check_in_time.value = data.check_in_time;
            check_out_time.value = data.check_out_time;
            contact_number.value = data.contact_info?.phone || '';
            image_url.value = data.image_url;
        }
    } catch (error) {
        console.error('Error fetching accommodation:', error);
        toast.error(error.response?.data?.message || 'Error loading accommodation details');
    } finally {
        isLoading.value = false;
    }
});

// Helper function to get full storage URL
function getStorageUrl(path) {
    if (!path) return '/placeholder.jpg';
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
}
</script>