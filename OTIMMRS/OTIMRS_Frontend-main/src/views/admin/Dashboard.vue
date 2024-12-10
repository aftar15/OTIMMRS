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
                        Hello, {{ auth.admin?.name || 'Admin' }}!
                        <img :src="auth.admin?.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="parallax">
                <h1>{{ time }}</h1>
                <h3>{{ moment().format('dddd, MMMM D') }}</h3>
            </div>
            <div class="pager-div">
                <div class="page-container">
                    <h1>Tourist Performance</h1>
                    <div class="performance-grid">
                        <div class="item">
                            <img src="../../assets/img/Location search-amico.svg" alt="">
                            <h3>Tourist Destinations</h3>
                            <h2> Destinations</h2>
                            <router-link to="/admin/attractions">View Destinations</router-link>
                        </div>
                        <div class="item">
                            <img src="../../assets/img/Team spirit-amico.svg" alt="">
                            <h3>Tourist Activities</h3>
                            <h2> Activities</h2>
                            <router-link to="/admin/activities">View Activities</router-link>
                        </div>
                        <div class="item">
                            <img src="../../assets/img/Hotel Booking-rafiki.svg" alt="">
                            <h3>Tourist Accommodations</h3>
                            <h2> Accommodations</h2>
                            <router-link to="/admin/accommodations">View Accommodations</router-link>
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
}

.login-container {
    padding-left: 350px;
    min-height: 100vh;



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
                    background-color: rgba(0, 0, 0, 0.152);
                    border-radius: 6px;
                    color: white;
                    outline: 0;
                    transition: all .2s cubic-bezier(0.165, 0.84, 0.44, 1);

                    &::placeholder {
                        color: rgba(255, 255, 255, 0.604);
                    }

                    &:hover,
                    &:active,
                    &:focus {
                        background-color: rgba(0, 0, 0, 0.285);
                    }

                    &:focus {
                        border-bottom: 2px solid #F2EA2A;
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
                    color: rgba(255, 255, 255, 0.563);
                }
            }
        }

        .profile-container {
            width: 30%;
            text-align: right;
            padding: 15px 30px;

            a {
                color: white;
                text-decoration: none;
                display: inline-block;
                padding: 10px 20px;
                border-radius: 7px;
                transition: all .1s linear;
                background-color: rgba(0, 0, 0, 0.185);

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

    .parallax {
        
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.422) 15%, rgba(0, 0, 0, 0.422) 15%), url(@/assets/img/background.jpg) no-repeat center center;
        background-size: cover;
        background-attachment: fixed;
        padding: 20px;
        height: 800px;
        margin-top: -465px;
        z-index: -1;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding-top: 465px;
        padding-bottom: 60px;

        h1 {
            color: white;
            font-weight: normal;
            font-size: 70px;
            // background-color: rgba(255, 255, 255, 0.708);
            color: white;
            padding: 10px;
            // margin: 30px 0;
            letter-spacing: 3px;
            font-style: italic;
        }

        h3 {
            color: white;
            font-weight: normal;
            font-size: 30px;
            // background-color: rgba(255, 255, 255, 0.708);
            color: white;
            padding: 10px;
            // margin: 30px 0;
            margin-top: -30px;
            margin-bottom: 30px;
            letter-spacing: 3px;
            font-style: italic;
        }
    }

    .pager-div {
        background-color: #F2F3F9;
        min-height: calc(100vh - 405px);
        border-top-right-radius: 50px;
        border-top-left-radius: 50px;
        margin-top: -60px;
        box-shadow: 0 -6px 10px rgba(0, 0, 0, 0.229);
    }

    .page-container {
        padding: 80px 30px;
        width:100%;
        max-width: 1400px;
        margin: auto;

        h1 {
            font-size: 25px;
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .performance-grid {
            display: flex;

            .item {
                width: calc(100%/3);
                padding: 30px;
                text-align: center;

                &:hover {
                    background-color: rgba(0, 0, 0, 0.056);
                    border-radius: 6px;
                }

                img {
                    width: 100%;
                    max-width: 200px;
                }

                h3 {
                    font-size: 14px;
                    margin: 10px 0;
                    color: #939393;
                }

                h2 {
                    font-size: 23px;
                    color: #131010;
                }

                a {
                    margin-top: 40px;
                    display: inline-block;
                    padding: 10px 20px;
                    text-decoration: none;
                    background-color: #2A9DF2;
                    color: white;
                    border-radius: 6px;
                    font-weight: bold;
                    letter-spacing: 2px;
                    font-size: 13px;
                    text-transform: uppercase;
                    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.156);
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);

                    &:hover {
                        opacity: 0.7;
                    }

                    &:active {
                        transform: scale(0.9);
                    }
                }
            }
        }
    }
}
</style>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AdminSidenav from '@/components/AdminSidenav.vue'
import moment from 'moment'

const auth = useAuthStore()
const time = ref('')

function updateTime() {
    time.value = moment().format('hh:mm:ss A')
}

onMounted(() => {
    updateTime()
    setInterval(updateTime, 1000)
})
</script>