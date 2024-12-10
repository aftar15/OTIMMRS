import './assets/css/main.css'
import 'vue-toastification/dist/index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import Toast from "vue-toastification"
import toast from '@/plugins/toast'
import { makeRequest } from '@/plugins/axios'

import App from './App.vue'
import router from './router'

// Create app instance
const app = createApp(App)

// Initialize Pinia first
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

// Configure Toast
const toastOptions = {
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
}

// Add Toast plugin
app.use(Toast, toastOptions)

// Initialize toast after Vue app is created
toast.init()

// Add router
app.use(router)

// Add global properties
app.config.globalProperties.$axios = makeRequest

// Add global error handler
app.config.errorHandler = (error, vm, info) => {
    console.error('Global error:', error)
    console.error('Component:', vm)
    console.error('Info:', info)

    // Show error toast
    if (error.response?.data?.message) {
        toast.error(error.response.data.message)
    } else if (error.message) {
        toast.error(error.message)
    } else {
        toast.error('An unexpected error occurred')
    }

    // Handle session expiry
    if (error.response?.status === 401) {
        router.push('/admin')
    }
}

// Mount the app
app.mount('#app')
