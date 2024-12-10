<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import toast from '@/plugins/toast'

const auth = useAuthStore()
const router = useRouter()

// Check session validity periodically
let sessionCheckInterval

onMounted(() => {
  // Check session every minute
  sessionCheckInterval = setInterval(async () => {
    // Check tourist session
    if (auth.isAuthenticated) {
      try {
        const isValid = await auth.checkAuth()
        if (!isValid) {
          toast.error('Tourist session expired. Please log in again.')
          router.push('/login')
        }
      } catch (error) {
        console.error('Tourist session check error:', error)
      }
    }
    
    // Check admin session
    if (auth.isAdminAuthenticated) {
      try {
        const isValid = await auth.checkAdminAuth()
        if (!isValid) {
          toast.error('Admin session expired. Please log in again.')
          router.push('/admin')
        }
      } catch (error) {
        console.error('Admin session check error:', error)
      }
    }
  }, 60000) // 1 minute
})

onUnmounted(() => {
  if (sessionCheckInterval) {
    clearInterval(sessionCheckInterval)
  }
})
</script>

<template>
  <router-view v-slot="{ Component }">
    <transition name="fade" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
