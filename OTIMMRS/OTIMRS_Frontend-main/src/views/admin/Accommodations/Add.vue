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
                            Tourist Accommodations
                        </h1>
                    </div>
                    <div class="action">
                        <button @click="addAccommodation()" class="button">
                            <i class="material-icons">add</i>
                            Add Accommodation
                        </button>
                    </div>
                    
                </div>
                <!-- <div class="skeleton" style="text-align:center">
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
                </div> -->
                
                <h2 class="subheader">Accommodation Information</h2>
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
                        <input v-model="location" type="text" placeholder="Location">
                        <i class="material-icons">location_on</i>
                    </div>
                    <div class="input-container one-third">
                        <select v-model="category">
                            <option value="" disabled selected>-- Select Category --</option>
                            <option value="Hotel">Hotel</option>
                        </select>
                        <i class="material-icons">category</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="opening_hours" type="text" placeholder="Opening Hours">
                        <i class="material-icons">access_time</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="admission_fee" type="text" placeholder="Admission Fee">
                        <i class="material-icons">attach_money</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="image_url" type="text" placeholder="Image URL">
                        <i class="material-icons">insert_photo</i>
                    </div>
                    <!-- <div class="input-container one-third">
                        <input v-model="rating" type="number" placeholder="Rating">
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
                        <input v-model="contact_phone" type="text" placeholder="Contact Phone">
                        <i class="material-icons">phone</i>
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
            cursor: pointer;

            &:hover {
                opacity: 0.8;
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

const route = useRoute();
const info = ref('loading...');

const name = ref('');
const description = ref('');
const location = ref('');
const category = ref('Hotel');
const opening_hours = ref('');
const admission_fee = ref('');
const image_url = ref('');
const rating = ref(5);
const latitude = ref('1');
const longitude = ref('1');
const map_source = ref('');
const contact_phone = ref('');

// create an makeRequest axios that submits the form to POST /tourist-attractions/add

async function addAccommodation() {
    await makeRequest.post('/tourist-attractions/add', {
        name: name.value,
        description: description.value,
        location: location.value,
        category: category.value,
        opening_hours: opening_hours.value,
        admission_fee: admission_fee.value,
        image_url: image_url.value,
        rating: rating.value,
        latitude: latitude.value,
        longitude: longitude.value,
        map_source: map_source.value,
        contact_phone: contact_phone.value,
    })

    toast.fire({
        title: 'Tourist Accommodation added',
        icon: 'success'
    })

    router.push('/admin/accommodations');
}

onMounted(() => {

    // Get the id from the router parameter
    const id = route.params.id;

    // make request using makeRequest from plugins/axios and get /tourists and put it in a reference
    makeRequest.get(`/tourists/${id}`).then(res => {
        if (res.data.success) {
            info.value = res.data.data;
        }
    })
})
</script>