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
                <h1>
                    <a href="/admin/tourist-information" class="icon-button">
                        <i class="material-icons">
                            arrow_back
                        </i>
                    </a>
                    {{ info.first_name ? `${info.first_name} ${info.middle_name || ''} ${info.last_name}` : info.full_name ? `${info.full_name}` : 'Loading ...' }}
                </h1>
                <div class="skeleton" v-if="info == 'loading...'" style="text-align:center">
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
                
                <div class="informations" v-else>
    
                    <h2 class="subheader">Personal Information</h2>
    
                    <div class="input-group">
                        <div class="input-container">
                            <input v-model="info.email" type="text" placeholder="Email Address (@gmail.com)">
                            <i class="material-icons">email</i>
                        </div>
                        <div class="input-container">
                            <input v-model="info.full_name" type="text" placeholder="Full Name">
                            <i class="material-icons">person</i>
                        </div>
                        <div class="input-container">
                            <input v-model="info.password" type="text" placeholder="Password">
                            <i class="material-icons">lock</i>
                        </div>
                       
                        
                        <div class="input-container one-third">
                            <!-- <input type="text" placeholder="Gender"> -->
                            <select v-model="info.gender" name="gender" id="gender">
                                <option value="" selected disabled>Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <i class="material-icons">wc</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="info.birthdate" type="date" placeholder="Birth Date">
                            <i class="material-icons">event</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="info.address" type="text" placeholder="Address">
                            <i class="material-icons">location_on</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="info.nationality" type="text" placeholder="Nationality">
                            <i class="material-icons">language</i>
                        </div>
                    </div>
                    <h2 class="subheader">Acommodation</h2>
                    <div class="input-group">
                        <div class="input-container one-third">
                            <input v-model="info.name" type="text" placeholder="Accommodation Name">
                            <i class="material-icons">business</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="info.location" type="text" placeholder="Accommodation Location">
                            <i class="material-icons">location_on</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="info.days" type="text" placeholder="How many days">
                            <i class="material-icons">event_available</i>
                        </div>
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
            margin-bottom: 50px;

            .icon-button {
                padding: 5px 10px;
                display: inline;
                // outline: 1px solid red;
                // background-color: rgb(227, 227, 227);
                border-radius: 50%;
                transition: all .3s cubic-bezier(0.175, 0.885, 0.32, 1.275);

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
import { useAuthStore } from '@/stores/auth';
import {useRoute, useRouter} from 'vue-router';
import { makeRequest } from '@/plugins/axios';
import toast from '@/plugins/toast';
import AdminSidenav from '@/components/AdminSidenav.vue';
import { onMounted, ref } from 'vue';

const auth = useAuthStore();
const router = useRouter();

async function logOut() {
    
    await makeRequest.get('/logout');
    auth.admin = {};

    toast.fire({
        title: 'Logged out',
        icon: 'info'
    })
    router.push('/admin');
}

const route = useRoute()
const info = ref('loading...')

const fetchTouristDetails = async () => {
    try {
        const touristId = route.params.id
        if (!touristId) {
            throw new Error('No tourist ID provided')
        }

        const response = await makeRequest.get(`/admin/tourists/${touristId}`)
        
        // Normalize response handling
        const touristData = response.data.success 
            ? response.data.data 
            : response.data

        info.value = {
            id: touristData.id || touristId,
            email: touristData.email,
            full_name: touristData.full_name || touristData.name,
            gender: touristData.gender,
            birthdate: touristData.birthdate,
            address: touristData.address,
            nationality: touristData.nationality,
            name: touristData.accommodation_name,
            location: touristData.accommodation_location,
            days: touristData.accommodation_days,
            password: '' // Never pre-fill password
        }
    } catch (error) {
        console.error('Error fetching tourist details:', error)
        toast.error('Failed to load tourist details')
        info.value = {}
    }
}

onMounted(fetchTouristDetails)
</script>