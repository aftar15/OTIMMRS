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
                        Hello, {{ auth?.admin?.name || 'Admin' }}!
                        <img :src="auth?.admin?.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>Admin Management</h1>
                        <p>{{ loading ? 'Loading...' : (admins ? admins.length : 0) }} admin(s)</p>
                    </div>
                    <div class="action">
                        <router-link to="/admin/add" class="button">
                            <i class="material-icons">add</i>
                            Add Admin
                        </router-link>
                    </div>
                </div>
                <div class="performance-grid" v-if="loading">
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
                <div class="performance-grid" v-else-if="error">
                    <div class="error-message">
                        {{ error }}
                    </div>
                </div>
                <div class="performance-grid" v-else-if="admins && admins.length > 0">
                    <router-link 
                        :to="{ name: 'admin/edit', params: { id: admin.id }}" 
                        class="item" 
                        v-for="admin in admins" 
                        :key="admin.id"
                    >
                        <img :src="admin.profile_picture || '/user.jpg'" alt="">
                        <h2>{{ admin.name }}</h2>
                        <span class="tags">
                            <i class="material-icons">alternate_email</i>
                            {{ admin.username }}
                        </span><br>
                        <span class="tags">
                            <i class="material-icons">date_range</i>
                            Joined on {{ moment(admin.created_at).format('MMMM DD, YYYY hh:mm:ss A') }}
                        </span>
                    </router-link>
                </div>
                <div class="performance-grid" v-else>
                    <div class="no-data">
                        No admins found.
                    </div>
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

.login-container {
    padding-left: 350px;

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

                    i {
                        vertical-align: middle;
                        margin-right: 5px;
                    }
                }
            }
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;

            .item {
                background-color: white;
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                text-decoration: none;
                color: #131010;
                transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);

                &:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }

                &:active {
                    transform: scale(0.95);
                }

                img {
                    width: 100%;
                    max-width: 150px;
                    border-radius: 50%;
                    margin-bottom: 20px;
                }

                h2 {
                    font-size: 18px;
                    margin-bottom: 10px;
                }

                .tags {
                    display: inline-block;
                    color: #939393;
                    font-size: 14px;
                    margin-bottom: 5px;

                    i {
                        font-size: 16px;
                        vertical-align: middle;
                        margin-right: 5px;
                    }
                }
            }

            .skeleton {
                cursor: progress;
                background: #fff;
                border-radius: 10px;
                min-height: 261px;
                overflow: hidden;
                margin: 10px auto;
                position: relative;

                svg {
                    width: 100%;
                }
            }

            .error-message {
                font-size: 18px;
                color: #939393;
                text-align: center;
                padding: 20px;
            }

            .no-data {
                font-size: 18px;
                color: #939393;
                text-align: center;
                padding: 20px;
            }
        }
    }
}
</style>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { makeRequest } from '@/plugins/axios'
import AdminSidenav from '@/components/AdminSidenav.vue'
import moment from 'moment'

const auth = useAuthStore()
const admins = ref(null)
const loading = ref(false)
const error = ref(null)

const loadAdmins = async () => {
    loading.value = true
    error.value = null
    try {
        const response = await makeRequest.get('/api/admin/admins')
        admins.value = response.data.data
    } catch (err) {
        console.error('Failed to load admins:', err)
        error.value = err.response?.data?.message || 'Failed to load admins'
        admins.value = null
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadAdmins()
})
</script>