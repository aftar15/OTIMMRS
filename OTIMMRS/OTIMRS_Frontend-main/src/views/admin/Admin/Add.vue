<template>
    <div class="container" v-if="auth?.admin">
        <admin-sidenav></admin-sidenav>
        <div class="login-container">
            <div class="navbar">
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
                        <h1>
                            <router-link to="/admin/management" class="icon-button">
                                <i class="material-icons">
                                    arrow_back
                                </i>
                            </router-link>
                            Add New Admin
                        </h1>
                    </div>
                    <div class="action">
                        <button @click="addAdmin()" class="button" :disabled="loading">
                            <i class="material-icons">add</i>
                            {{ loading ? 'Adding...' : 'Add Admin' }}
                        </button>
                    </div>
                    
                </div>
                <h2 class="subheader">Admin Information</h2>
                <div class="input-group">
                    <div class="input-container one-third">
                        <input v-model="formData.name" type="text" placeholder="Full Name" required>
                        <i class="material-icons">person</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="formData.email" type="email" placeholder="Email Address" required>
                        <i class="material-icons">email</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="formData.profile_picture" type="url" placeholder="Profile Picture URL (Must be SQUARE)" required>
                        <i class="material-icons">insert_photo</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="formData.username" type="text" placeholder="Username" required>
                        <i class="material-icons">person</i>
                    </div>
                    <div class="input-container one-third">
                        <input v-model="formData.password" type="password" placeholder="Password" required>
                        <i class="material-icons">lock</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else>
        <p>Please login first</p>
        <router-link to="/admin/login">Go to Login</router-link>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import AdminSidenav from '@/components/AdminSidenav.vue'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const router = useRouter()
const loading = ref(false)

onMounted(async () => {
    if (!auth.admin) {
        const isValid = await auth.checkAdminAuth()
        if (!isValid) {
            toast.error('Please login first')
            router.push('/admin/login')
        }
    }
})

const formData = reactive({
    name: '',
    email: '',
    username: '',
    password: '',
    profile_picture: ''
})

async function addAdmin() {
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        toast.fire({
            title: 'Please enter a valid email address',
            icon: 'error'
        });
        return;
    }

    // Validate required fields
    if (!formData.name || !formData.email || !formData.username || !formData.password) {
        toast.fire({
            title: 'Please fill in all required fields',
            icon: 'error'
        });
        return;
    }

    // Validate password length
    if (formData.password.length < 6) {
        toast.fire({
            title: 'Password must be at least 6 characters long',
            icon: 'error'
        });
        return;
    }

    try {
        loading.value = true;
        // Log the request data for debugging
        console.log('Sending request with data:', {
            name: formData.name.trim(),
            email: formData.email.trim().toLowerCase(),
            username: formData.username.trim(),
            password: formData.password,
            profile_picture: formData.profile_picture?.trim() || null
        });

        const response = await makeRequest.post('/api/admin/admins', {
            name: formData.name.trim(),
            email: formData.email.trim().toLowerCase(),
            username: formData.username.trim(),
            password: formData.password,
            profile_picture: formData.profile_picture?.trim() || null
        });
        
        if (response.data.success) {
            toast.fire({
                title: 'New Admin added successfully',
                icon: 'success'
            });
            router.push('/admin/management');
        } else {
            throw new Error(response.data.message || 'Failed to add admin');
        }
    } catch (error) {
        console.error('Error adding admin:', error.response?.data || error.message);
        
        // Handle specific error cases
        if (error.response?.status === 422) {
            // Validation error
            const errors = error.response.data.errors;
            const errorMessage = Object.values(errors).flat().join('\n');
            toast.fire({
                title: 'Validation Error',
                text: errorMessage,
                icon: 'error'
            });
        } else if (error.response?.status === 401) {
            // Unauthorized
            toast.fire({
                title: 'Session expired',
                text: 'Please login again',
                icon: 'error'
            });
            router.push('/admin/login');
        } else {
            // General error
            toast.fire({
                title: error.response?.data?.message || 'Failed to add admin',
                text: 'Please check your input and try again',
                icon: 'error'
            });
        }
    } finally {
        loading.value = false;
    }
}
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
        padding: 15px 30px;
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

        .search-container {
            flex: 1;
        }

        .profile-container {
            a {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                color: #333;
                padding: 8px 16px;
                border-radius: 8px;
                transition: all 0.3s;

                &:hover {
                    background: rgba(0, 0, 0, 0.05);
                }

                img {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                }
            }
        }
    }
}

.page-container {
    padding: 30px;
    width: 100%;
    max-width: 1400px;
    margin: auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;

    .titles {
        h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            margin: 0;

            .icon-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 8px;
                color: #666;
                text-decoration: none;
                transition: all 0.3s;

                &:hover {
                    background: rgba(0, 0, 0, 0.05);
                }
            }
        }
    }

    .action {
        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2A9DF2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;

            &:hover:not(:disabled) {
                background: darken(#2A9DF2, 10%);
            }

            &:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
        }
    }
}

.subheader {
    font-size: 18px;
    color: #666;
    margin-bottom: 20px;
}

.input-group {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;

    .input-container {
        position: relative;
        flex: 1;
        min-width: 200px;

        &.one-third {
            flex-basis: calc(33.333% - 14px);
        }

        input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 45px;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;

            &:focus {
                outline: none;
                border-color: #2A9DF2;
                box-shadow: 0 0 0 3px rgba(42, 157, 242, 0.1);
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
</style>