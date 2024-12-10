<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar bg-gray-800 text-white w-64 min-h-screen fixed">
      <div class="p-4">
        <h1 class="text-xl font-bold">OTIMMRS Admin</h1>
      </div>
      
      <nav class="mt-8">
        <router-link 
          v-for="item in menuItems" 
          :key="item.path"
          :to="item.path"
          class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white"
          :class="{ 'bg-gray-700 text-white': isActive(item.path) }"
        >
          <i :class="item.icon" class="mr-3"></i>
          {{ item.name }}
        </router-link>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'AdminLayout',
  setup() {
    const route = useRoute()

    const menuItems = [
      { 
        name: 'Dashboard', 
        path: '/admin/dashboard',
        icon: 'fas fa-tachometer-alt'
      },
      { 
        name: 'Reports', 
        path: '/admin/reports',
        icon: 'fas fa-chart-bar'
      },
      { 
        name: 'Tourist Information', 
        path: '/admin/tourist-information',
        icon: 'fas fa-users'
      },
      { 
        name: 'Attractions', 
        path: '/admin/attractions',
        icon: 'fas fa-map-marker-alt'
      },
      { 
        name: 'Activities', 
        path: '/admin/activities',
        icon: 'fas fa-hiking'
      },
      { 
        name: 'Accommodations', 
        path: '/admin/accommodations',
        icon: 'fas fa-bed'
      },
      { 
        name: 'Arrivals', 
        path: '/admin/arrivals',
        icon: 'fas fa-plane-arrival'
      },
      { 
        name: 'Admin Management', 
        path: '/admin/management',
        icon: 'fas fa-user-shield'
      }
    ]

    const isActive = (path) => {
      return route.path.startsWith(path)
    }

    return {
      menuItems,
      isActive
    }
  }
}
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.sidebar {
  box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
}
</style>
