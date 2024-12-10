<template>
    <div class="login-container">
        <img src="../../assets/img/admin-logo.svg" alt="">
        <div class="texts">
            <i class="material-icons">safety_check</i>
            <h3>Admin Access v1.3</h3>
            
            <div v-if="error" class="error-message">
                {{ error }}
            </div>
            
            <div class="input-group">
                <div class="input-container">
                    <input 
                        v-model="username" 
                        type="text" 
                        placeholder="Username"
                        :disabled="loading"
                    >
                    <i class="material-icons">person</i>
                </div>
                <div class="input-container">
                    <input 
                        v-model="password" 
                        type="password" 
                        placeholder="Password"
                        :disabled="loading"
                        @keyup.enter="login"
                    >
                    <i class="material-icons">key</i>
                </div>
            </div>
            
            <div style="text-align: center">
                <button 
                    @click="login" 
                    class="submit-button"
                    :disabled="loading || !username || !password"
                >
                    <i class="material-icons">lock</i>
                    {{ loading ? 'LOGGING IN...' : 'LOGIN' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Toast from '../../plugins/toast'
import { useAuthStore } from '../../stores/auth'

const username = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const router = useRouter()
const auth = useAuthStore()

const login = async () => {
    try {
        loading.value = true
        error.value = ''
        
        // Log the request
        console.log('Login attempt:', { username: username.value })

        const success = await auth.adminLogin({
            username: username.value,
            password: password.value
        })

        if (success) {
            await Toast.fire({
                icon: 'success',
                title: 'Login successful'
            })
            router.push('/admin/dashboard')
        } else {
            error.value = 'Login failed. Please check your credentials.'
            await Toast.fire({
                icon: 'error',
                title: error.value
            })
        }
    } catch (err) {
        console.error('Login error:', err)
        error.value = err.message || 'Login failed. Please check your credentials.'
        await Toast.fire({
            icon: 'error',
            title: error.value
        })
    } finally {
        loading.value = false
    }
}
</script>

<style scoped lang="scss">
.login-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: radial-gradient(circle, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.73) 100%), 
                url(../../assets/img/background.jpg) no-repeat center center;
    background-size: cover;

    img {
        width: 100%;
        max-width: 150px;
        margin-bottom: 30px;
    }

    .texts {
        background-color: white;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;

        i.material-icons {
            font-size: 50px;
            color: #2A9DF2;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 20px;
            margin-bottom: 30px;
            color: #131010;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 30px;

            .input-container {
                position: relative;
                margin-bottom: 20px;

                input {
                    width: 100%;
                    padding: 15px;
                    padding-left: 50px;
                    border: 2px solid #e0e0e0;
                    border-radius: 5px;
                    font-size: 16px;
                    transition: all 0.3s ease;

                    &:focus {
                        border-color: #2A9DF2;
                        outline: none;
                    }

                    &:disabled {
                        background-color: #f5f5f5;
                        cursor: not-allowed;
                    }
                }

                i.material-icons {
                    position: absolute;
                    left: 15px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 24px;
                    color: #757575;
                }
            }
        }

        .submit-button {
            background-color: #2A9DF2;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;

            &:hover:not(:disabled) {
                background-color: #1976D2;
            }

            &:disabled {
                background-color: #90CAF9;
                cursor: not-allowed;
            }

            i.material-icons {
                font-size: 20px;
            }
        }
    }
}
</style>