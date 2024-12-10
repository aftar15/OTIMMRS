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
                            <a href="/admin/activities" class="icon-button">
                                <i class="material-icons">
                                    arrow_back
                                </i>
                            </a>
                            {{ isLoading ? 'Loading...' : name }}
                        </h1>
                    </div>
                    <div class="action">
                        <button class="icon-button" @click="deleteAttraction()" title="Delete Attraction">
                            <i class="material-icons">delete</i>
                        </button>
                        <button @click="saveAttraction()" class="button">
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
                    
                    <h2 class="subheader">Activity Information</h2>
                <div class="input-group">
                    <div class="input-container one-third">
                        <input v-model="name" type="text" placeholder="Attraction Name" required>
                        <i class="material-icons">business</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="description" placeholder="Description" required>
                        <i class="material-icons">message</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="location" type="text" placeholder="Location" required>
                        <i class="material-icons">location_on</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="due_date" type="date" placeholder="Due Date" required>
                        <i class="material-icons">event</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="cost" type="number" placeholder="Admission Fee" step=".01">
                        <i class="material-icons">attach_money</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="image_url" type="url" placeholder="Image URL" required>
                        <i class="material-icons">insert_photo</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="capacity" type="number" placeholder="Capacity" min="0" max="5" step="0.5">
                        <i class="material-icons">people</i>
                    </div>
                    <!-- <div class="input-container one-third">
                        <input v-model="rating" type="number" placeholder="Rating" min="0" max="5" step="0.5">
                        <i class="material-icons">grade</i>
                    </div> -->
                    <div class="input-container one-third">
                        <input v-model="map_source" type="text" placeholder="Map Source">
                        <i class="material-icons">my_location</i>
                    </div>
                    <!-- <div class="input-container one-third">
                        <input v-model="latitude" type="number" placeholder="Latitude">
                        <i class="material-icons">my_location</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="longitude" type="number" placeholder="Longitude">
                        <i class="material-icons">location_on</i>
                    </div> -->
                    <div class="input-container one-third">
                        <input v-model="contact_phone" type="tel" placeholder="Contact Phone">
                        <i class="material-icons">phone</i>
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
</style>

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
const error = ref(null)

// Form fields
const name = ref('')
const description = ref('')
const location = ref('')
const due_date = ref('')
const cost = ref(0)
const capacity = ref(0)
const image_url = ref('')
const map_source = ref('')
const contact_phone = ref('')
const comments = ref([])

const loadActivity = async () => {
    try {
        isLoading.value = true
        error.value = null
        const response = await makeRequest.get(`/admin/activities/${route.params.id}`)
        const activity = response.data.data
        
        // Map backend data to form fields
        name.value = activity.name || ''
        description.value = activity.description || ''
        location.value = activity.location || ''
        due_date.value = activity.due_date ? activity.due_date.split('T')[0] : ''
        cost.value = activity.cost || 0
        capacity.value = activity.capacity || 0
        image_url.value = activity.image_url || ''
        map_source.value = activity.map_source || ''
        contact_phone.value = activity.contact_phone || ''
        comments.value = activity.comments || []
    } catch (err) {
        console.error('Failed to load activity:', err)
        error.value = err.response?.data?.message || 'Failed to load activity details'
        alert(error.value)
    } finally {
        isLoading.value = false
    }
}

const validateForm = () => {
    if (!name.value) return 'Name is required'
    if (!description.value) return 'Description is required'
    if (!location.value) return 'Location is required'
    if (cost.value < 0) return 'Cost must be greater than or equal to 0'
    if (capacity.value < 1) return 'Capacity must be at least 1'
    return null
}

const saveAttraction = async () => {
    try {
        error.value = null
        
        // Validate form
        const validationError = validateForm()
        if (validationError) {
            error.value = validationError
            alert(error.value)
            return
        }

        // Prepare data for backend
        const data = {
            name: name.value,
            description: description.value,
            location: location.value,
            due_date: due_date.value || null,
            cost: parseFloat(cost.value),
            capacity: parseInt(capacity.value),
            image_url: image_url.value,
            map_source: map_source.value,
            contact_phone: contact_phone.value
        }

        await makeRequest.put(`/admin/activities/${route.params.id}`, data)
        alert('Activity updated successfully')
        router.push('/admin/activities')
    } catch (err) {
        console.error('Failed to update activity:', err)
        error.value = err.response?.data?.message || 'Failed to update activity'
        alert(error.value)
    }
}

const deleteAttraction = async () => {
    if (!confirm('Are you sure you want to delete this activity?')) {
        return
    }
    
    try {
        error.value = null
        await makeRequest.delete(`/admin/activities/${route.params.id}`)
        alert('Activity deleted successfully')
        router.push('/admin/activities')
    } catch (err) {
        console.error('Failed to delete activity:', err)
        error.value = err.response?.data?.message || 'Failed to delete activity'
        alert(error.value)
    }
}

onMounted(() => {
    loadActivity()
})
</script>