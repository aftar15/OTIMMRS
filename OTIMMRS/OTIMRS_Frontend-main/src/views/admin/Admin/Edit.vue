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
                        Hello, {{ auth.admin.name }}!
                        <img :src="auth.admin.profile_picture || '/user.jpg'" alt="">
                    </a>
                </div>
            </div>
            <div class="page-container">
                <div class="header">
                    <div class="titles">
                        <h1>
                            <a href="/admin/management" class="icon-button">
                                <i class="material-icons">
                                    arrow_back
                                </i>
                            </a>
                            {{ isLoading ? 'Loading...' : name }}
                        </h1>
                    </div>
                    <div class="action">
                        <button class="icon-button" @click="deleteAdmin()" title="Delete Admin">
                            <i class="material-icons">delete</i>
                        </button>
                        <button @click="saveAdmin()" class="button">
                            <i class="material-icons">save</i>
                            Save Changes
                        </button>
                    </div>
                </div>
                <div v-if="isLoading" class="skeleton" style="text-align:center">
                    Loading...
                </div>
                <div v-else>
                    <h2 class="subheader">Admin Information</h2>
                    <div class="input-group">
                        <div class="input-container one-third">
                            <input v-model="name" type="text" placeholder="Name" required>
                            <i class="material-icons">person</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="email" type="email" placeholder="Email" required>
                            <i class="material-icons">email</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="username" type="text" placeholder="Username" required>
                            <i class="material-icons">account_circle</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="profile_picture" type="url" placeholder="Profile Picture URL">
                            <i class="material-icons">image</i>
                        </div>
                        <div class="input-container one-third">
                            <input v-model="password" type="password" placeholder="New Password">
                            <i class="material-icons">lock</i>
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
            padding: 15px;

            .input-container {
                position: relative;
                width: 100%;
                max-width: 800px;
                margin: auto;
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
            align-items: center;
            margin-bottom: 50px;

            .titles {
                h1 {
                    font-size: 25px;
                    margin-top: 30px;
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

                    i {
                        vertical-align: middle;
                        margin-right: 5px;
                    }
                }

                .icon-button {
                    display: inline-block;
                    color: #c73b3b;
                    padding: 13px;
                    border-radius: 50%;
                    text-decoration: none;
                    transition: all .3s cubic-bezier(0.165, 0.84, 0.44, 1);
                    border: 0;
                    margin: 0 10px;
                    cursor: pointer;

                    &:hover {
                        background-color: rgba(199, 59, 59, 0.1);
                    }

                    &:active {
                        transform: scale(0.9);
                    }
                }
            }
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .input-container {
            position: relative;
            flex: 1;
            min-width: 300px;

            &.one-third {
                flex-basis: calc(33.333% - 20px);
            }

            input {
                width: 100%;
                padding: 15px 20px;
                padding-left: 50px;
                border: 1px solid #E5E5E5;
                border-radius: 7px;
                font-size: 14px;
                transition: all .3s ease;

                &:focus {
                    outline: none;
                    border-color: #2A9DF2;
                }
            }

            i {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #939393;
            }
        }
    }
}
</style>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminSidenav from '@/components/AdminSidenav.vue'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const name = ref('')
const email = ref('')
const username = ref('')
const password = ref('')
const profile_picture = ref('')

const loadAdmin = async () => {
    try {
        isLoading.value = true
        const response = await makeRequest.get(`/api/admin/admins/${route.params.id}`)
        
        if (response.data.success && response.data.data) {
            const admin = response.data.data
            name.value = admin.name || ''
            email.value = admin.email || ''
            username.value = admin.username || ''
            profile_picture.value = admin.profile_picture || ''
        } else {
            throw new Error('Invalid response format')
        }
    } catch (error) {
        console.error('Failed to load admin:', error)
        toast.fire({
            title: 'Failed to load admin details',
            text: error.response?.data?.message || 'Please try again later',
            icon: 'error'
        })
        router.push('/admin/management')
    } finally {
        isLoading.value = false
    }
}

const saveAdmin = async () => {
    try {
        if (!name.value || !email.value || !username.value) {
            toast.fire({
                title: 'Please fill in all required fields',
                icon: 'error'
            })
            return
        }

        const data = {
            name: name.value.trim(),
            email: email.value.trim().toLowerCase(),
            username: username.value.trim(),
            profile_picture: profile_picture.value?.trim() || null
        }

        // Only include password if it's being changed
        if (password.value) {
            if (password.value.length < 6) {
                toast.fire({
                    title: 'Password must be at least 6 characters long',
                    icon: 'error'
                })
                return
            }
            data.password = password.value
        }

        const response = await makeRequest.put(`/api/admin/admins/${route.params.id}`, data)
        
        if (response.data.success) {
            toast.fire({
                title: 'Admin updated successfully',
                icon: 'success'
            })
            router.push('/admin/management')
        } else {
            throw new Error(response.data.message || 'Failed to update admin')
        }
    } catch (error) {
        console.error('Error updating admin:', error)
        toast.fire({
            title: error.response?.data?.message || 'Failed to update admin',
            text: 'Please check your input and try again',
            icon: 'error'
        })
    }
}

const deleteAdmin = async () => {
    if (!confirm('Are you sure you want to delete this admin?')) {
        return
    }

    try {
        const response = await makeRequest.delete(`/api/admin/admins/${route.params.id}`)
        
        if (response.data.success) {
            toast.fire({
                title: 'Admin deleted successfully',
                icon: 'success'
            })
            router.push('/admin/management')
        } else {
            throw new Error(response.data.message || 'Failed to delete admin')
        }
    } catch (error) {
        console.error('Error deleting admin:', error)
        toast.fire({
            title: error.response?.data?.message || 'Failed to delete admin',
            text: 'Please try again later',
            icon: 'error'
        })
    }
}

onMounted(() => {
    loadAdmin()
})
</script>