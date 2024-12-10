<template>
    <div class="container" v-if="auth.admin">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
                <div class="search-container">
                    <div class="input-container">
                        <input type="text" placeholder="Search activities...">
                        <i class="material-icons">search</i>
                    </div>
                </div>
                <div class="profile-container">
                    <a href="#!" @click="logOut">
                        Hello, {{ auth.admin.first_name }}!
                        <img :src="auth.admin.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>Activities</h1>
                        <p>{{ activities.length }} Activity(s)</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/activities/add" class="button">
                            <i class="material-icons">add</i>
                            Add Activity
                        </router-link>
                    </div>
                    
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="performance-grid">
                    <div v-for="n in 12" :key="n" class="item-skeleton">
                        <div class="image-skeleton"></div>
                    </div>
                </div>

                <!-- Activities Grid -->
                <div v-else-if="activities.length > 0" class="performance-grid">
                    <router-link :to="`/admin/activities/${activity.id}`" class="item" v-for="(activity, i) in activities" :key="activity.id">
                        <div class="image-container" :style="`--image: url(${activity.image_url});`">
                            
                            <!-- <div class="edit-button">
                                <i class="material-icons">edit</i>
                            </div> -->
                        </div>
                        
                        <div class="informations">
                            <div class="texts">
                                <StarRatings :item="activity" />
                                <!-- <p>
                                    <i class="material-icons star" v-for="star in renderStars(activity)" :key="star.key">{{ star.icon }}</i>
                                </p> -->
                                <p class="date"><i class="material-icons">calendar_today</i> {{ moment(activity.due_date).diff(moment(), 'days') < 0 ? 'Already finished' : moment(activity.due_date).diff(moment(), 'days') + (moment(activity.due_date).diff(moment(), 'days') > 1 ? ' days' : ' day') + ' left' }}</p>
                                <h1>{{ activity.name }}</h1>
                                <p class="description">{{ activity.description }}</p>
                            </div>
                        </div>
                    </router-link>
                </div>

                <!-- Empty State -->
                <div v-else class="empty-state">
                    <span class="material-symbols-outlined">
                        hiking
                    </span>
                    <h2>No Activities Found</h2>
                    <p>Start by adding some activities to your collection</p>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="pagination">
                    <button 
                        :disabled="currentPage === 1"
                        @click="handlePageChange(currentPage - 1)"
                        class="page-btn"
                    >
                        Previous
                    </button>
                    <span>Page {{ currentPage }} of {{ totalPages }}</span>
                    <button 
                        :disabled="currentPage === totalPages"
                        @click="handlePageChange(currentPage + 1)"
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
import { useAuthStore } from '../../stores/auth';
import {useRouter} from 'vue-router';
import { makeRequest } from '../../plugins/axios';
import toast from '@/plugins/toast';
import { faker } from '@faker-js/faker';
import moment from 'moment';
import AdminSidenav from '@/components/AdminSidenav.vue';
import { onMounted, ref } from 'vue';
import StarRatings from '@/components/StarRatings.vue';

const auth = useAuthStore();
const router = useRouter();
const activities = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = 12;

async function logOut() {
    try {
        await makeRequest.get('/api/logout');
        auth.admin = {};
        toast.fire({
            title: 'Logged out',
            icon: 'info'
        })
        router.push('/admin');
    } catch (error) {
        console.error('Logout error:', error);
        toast.error('Failed to logout. Please try again.');
    }
}

async function fetchActivities(page = 1) {
    try {
        loading.value = true;
        const response = await makeRequest.get('/api/admin/activities', {
            params: {
                page,
                per_page: itemsPerPage
            }
        });
        
        if (response.data.success) {
            activities.value = response.data.data || [];
            totalPages.value = Math.ceil(response.data.total / itemsPerPage);
            currentPage.value = page;
        } else {
            throw new Error(response.data.message || 'Failed to fetch activities');
        }
    } catch (error) {
        console.error('Error fetching activities:', error);
        toast.fire({
            title: 'Error fetching activities',
            text: error.response?.data?.message || 'Please try again later',
            icon: 'error'
        });
        activities.value = [];
    } finally {
        loading.value = false;
    }
}

function handlePageChange(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchActivities(page);
    }
}

onMounted(() => {
    if (!auth.admin) {
        router.push('/admin');
        return;
    }
    fetchActivities();
});
</script>

<style scoped lang="scss">
.container {
    min-height: 100vh;
    position: relative;
    background-color: #F2F3F9;
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

                    &:hover {
                        opacity: 0.8;
                    }

                    &:active {
                        transform: scale(0.9);
                    }
                }
            }
        }


        .performance-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;

            .item {
                width: calc(100%/3 - 25px);
                text-align: center;
                cursor: pointer;
                transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                display: block;
                text-decoration: none;
                text-align: left;
                text-decoration: none;
                // background-color: rgba(0, 0, 0, 0.026);
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.158);
                border-radius: 10px;
                overflow: hidden;

                
                &:hover {
                    transform: scale(1.02);
                    box-shadow: 0 3px 20px rgba(0, 0, 0, 0.149);
                }

                &:active {
                    transform: scale(0.98);
                    box-shadow: 0 3px 4px rgba(0, 0, 0, 0.218);
                }

                .image-container {
                    width: 100%;
                    padding-bottom: 56%;
                    background:radial-gradient(circle, rgba(0, 0, 0, 0.084) 0%, rgba(0, 0, 0, 0.168) 100%), var(--image) no-repeat center center;
                    background-size: cover;
                    border-bottom-left-radius: 0;
                    border-bottom-right-radius: 0;
                    position: relative;
                    overflow: hidden;
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                    // background-color: red;

                    .edit-button {
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        display: inline-block;
                        margin: 20px;
                        // outline: 1px solid red;
                        color: white;
                        background-color: #2A9DF2;
                        padding: 13px;
                        border-radius: 50%;
                        box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.241);
                    }
                }

                
                    
                .informations {
                    color:#131010;
                    padding: 20px;
                    margin: 10px 0;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);;
                    

                    .texts {
                        width: 100%;

                        h1 {
                            font-size: 22px;
                        
                        }
    
                        .date {
                            // margin: 10px 0;
                            margin-bottom: 10px;
                            color: #939393;

                            i.material-icons {
                                color: #2A9DF2;
                                display: inline-block;
                                font-size: 20px;
                            }
                        }


                        p.description {
                            margin: 10px 0;
                            font-size: 16px;
                            opacity: 0.6;
                            // add max-height and truncate
                            max-height: 100px;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            max-width: 100%;
                        }

                    }

                    .icon-button {
                        display: inline-block;
                        padding: 10px;
                        // outline: 1px solid red;
                        transition: .2s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border-radius: 50%;
                        color: #2A9DF2;

                        &:hover {
                            background-color: rgba(19, 16, 16, 0.095);
                        }
                    }

                }
            }

            .item-skeleton {
                width: calc(100%/3 - 25px);
                text-align: center;
                transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                display: block;
                text-decoration: none;
                text-align: left;
                text-decoration: none;

                .image-skeleton {
                    width: 100%;
                    padding-bottom: 110%;
                    background:rgb(220, 220, 220);
                    background-size: cover;
                    border-radius: 10px;
                    // box-shadow: 0 3px 6px rgba(0, 0, 0, 0.158);
                    position: relative;
                    overflow: hidden;
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                    animation: color_change 1s infinite;
                }
            }
        }
    }
}

// create an animation that changes the background color every 2 seconds
@keyframes color_change {
    0% {
        background-color: rgb(220, 220, 220);
    }
    50% {
        background-color: rgb(234, 234, 234);
    }
    100% {
        background-color: rgb(220, 220, 220);
    }
}
</style>