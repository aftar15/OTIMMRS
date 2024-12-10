<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { makeRequest } from '@/plugins/axios'
import toast from '@/plugins/toast'

const router = useRouter()

const handleRegister = async () => {
  try {
    // Your registration logic here
    const response = await makeRequest.post('/register', formData)
    
    if (response.data.success) {
      toast.success('Registration successful!')
      router.push('/login')
    } else {
      toast.error(response.data.message || 'Registration failed')
    }
  } catch (error) {
    console.error('Registration error:', error)
    toast.error(error.response?.data?.message || 'An error occurred during registration')
  }
}
</script> 