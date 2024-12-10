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
                        <h1>Arrivals</h1>
                        <p> {{ displayDates }}: {{ tourists != 'loading...' ? tourists.length : '0' }} tourist(s)</p>
                    </div>
                    <div class="action">
                        <a href="#!" class="button picker">
                            <i class="material-icons">date_range</i>
                            {{ displayDates }}
                            <VueDatePicker class="date-picker-orig" @closed="getData()" :enable-time-picker="false" v-model="date_range" :range="true"></VueDatePicker>
                        </a>
                        <a :href="`/admin/papers/arrivals?from=${moment(date_range[0]).format('YYYY-MM-DD')}&to=${moment(date_range[1]).format('YYYY-MM-DD')}`" target="_blank" class="button">
                            <i class="material-icons">date_range</i>
                            Print
                        </a>
                    </div>
                </div>
                <div class="bordered-table">
                    <table>
                        <thead>
                            <th style="text-align: center">#</th>
                            <th>Tourist Name</th>
                            <th>From</th>
                            <th>Arrived on</th>
                        </thead>
                        <tbody>
                            <tr v-if="tourists == 'loading...'">
                                <td colspan="4" style="text-align: center"> <i class="material-icons">info</i> Data is loading ...</td>
                            </tr>
                            <tr v-else-if="tourists.length < 1">
                                <td colspan="4" style="text-align: center"><i class="material-icons">info</i> No tourists arived at {{ displayDates }}</td>
                            </tr>
                            <tr v-else v-for="(tourist, i) in tourists" :key="tourist.id">
                                <td style="text-align: right">{{ i+1 }}</td>
                                <td><i class="material-icons">person</i> {{ tourist.full_name ? tourist.full_name : `${tourist.first_name} ${tourist.last_name}` }}</td>
                                <td><i class="material-icons">place</i>{{ tourist.location }}</td>
                                <td><i class="material-icons">flight_land</i> {{ moment(tourist.created_at).format('MMMM DD, YYYY hh:mm:ss A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
:root {
    --dp-hover-color: #e1e1e1 !important;
    --dp-range-between-dates-background-color: #e1e1e1 !important;

}

.dp__main {
    width: 100%;
    display: flex;

    & > div {
        flex-grow: 1;
        width: 100%;

        &[disabled="true"] {
            position: absolute;
            top: 0; 
        }
    }
}



.dp__input_wrap {

    .dp__pointer {
        height: 100%;
        width: 100%;
        flex-grow: 1;
    }

    height: 100% !important;
    width: 100%;
    flex-grow: 1;
    opacity: 0 !important;
}
</style>

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
            margin-bottom: 50px;
            align-items: center;
            h1 {
                font-size: 25px;
                margin-top: 30px;
            }
            
            p {
                color: #939393;
            }

            .action {
                .button {
                    display: inline-block;
                    background-color: #2A9DF2;
                    color: #F2F3F9;
                    padding: 13px 20px;
                    border-radius: 7px;
                    margin: 0 10px;
                    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.082);
                    text-decoration: none;
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);


                    i.material-icons {
                        margin-right: 10px;
                    }

                    &:hover {
                        opacity: 0.8;
                    }

                    &:active {
                        transform: scale(0.9);
                    }

                    
                    &.picker {
                        background-color: white;
                        color: #131010;
                        box-shadow: none;
                        border: 1px dashed #A1A1A1;
                        position: relative;

                        &:active {
                            transform: scale(1);
                        }

                        .date-picker-orig {
                            position: absolute;
                            top: 0;
                            left: 0;
                            bottom: 0;
                            right: 0;
                            height: 100%;
                            z-index: 9;

                            .dp__input_wrap {
                                height: 100%;
                                opacity: 0 !important;
                            }
                        }
                    }
                }
            }
        }


        .performance-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;

            .item {
                width: calc(100% - 25px);
                padding: 30px;
                text-align: center;
                border: 1px dashed #A1A1A1;
                cursor: pointer;
                transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                display: flex;
                align-items: center;
                text-decoration: none;


                .contexts {
                    text-align: left;
                    padding: 0 15px;
                }

                &.skeleton {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px dashed #d9d9d9;
                }

                &:hover {
                    background-color: rgba(0, 0, 0, 0.056);
                    border-radius: 6px;
                }

                .person-icon {
                    font-size: 70px;
                    color: #666666;
                    background-color: white;
                    display: inline-block;
                    padding: 20px;
                    margin: 25px 0;
                }

                h2 {
                    color: #2A9DF2;
                    font-size: 20px;
                    margin-bottom: 20px;
                }

                .tags {
                    display: inline-block;
                    padding: 3px;
                    font-size: 15px;
                    // outline: 1px solid red;
                    // margin: 5px;
                    color: #939393;

                    i.material-icons {
                        margin-right: 10px;
                        color: #1310109f;
                    }
                }
            }


        }

        .bordered-table {
            
            border: 1px dashed #A1A1A1;
            border-radius: 10px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;

            th {
                color: #2A9DF2;
            }

            tr {
                &:hover {
                    background-color: #00000009;
                }
            }

            th, td {
                padding: 15px;
                border-bottom: 1px dashed #d4d4d4;

                i.material-icons {
                    color: #bababa;
                    margin-right: 10px;
                }
            }
        }
    }
}
</style>

<script setup>
import { useAuthStore } from '../../stores/auth';
import {useRouter} from 'vue-router';
import { makeRequest } from '../../plugins/axios';
import toast from '@/plugins/toast';
import { faker } from '@faker-js/faker';
import moment from 'moment';
import AdminSidenav from '@/components/AdminSidenav.vue';
import { computed, onMounted, ref } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

const auth = useAuthStore();
const router = useRouter();

const date_range = ref([new Date(), new Date()]);

async function logOut() {
    
    await makeRequest.get('/logout');
    auth.admin = {};

    toast.fire({
        title: 'Logged out',
        icon: 'info'
    })
    router.push('/admin');
}

const displayDates = computed(() => {

    // console.log(date_range.value[0], date_range.value[1])
    if (date_range.value[0] == date_range.value[1]) return moment(date_range.value[0]).format('MMMM DD, YYYY');

    return date_range.value ? moment(date_range.value[0]).format('MMMM DD, YYYY') + ' - ' + moment(date_range.value[1]).format('MMMM DD, YYYY')  : 'Today';
});

const tourists = ref('loading...');

function getData() {
    tourists.value = 'loading...';
    makeRequest.get(`/arrivals?from=${moment(date_range.value[0]).format('YYYY-MM-DD')}&to=${moment(date_range.value[1]).format('YYYY-MM-DD')}`).then(res => {
        if (res.data.success) {
            console.log('Requested!');
            tourists.value = res.data.data;
        }
    })
}

onMounted(() => {
    // make request using makeRequest from plugins/axios and get /tourists and put it in a reference
    getData();
})

</script>