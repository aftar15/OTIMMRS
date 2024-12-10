import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import AdminLogin from '../views/admin/Login.vue'
import AdminDashboard from '../views/admin/Dashboard.vue'
import AdminManagement from '../views/admin/AdminManagement.vue'
import TouristInformation from '../views/admin/TouristInformation.vue'
import TouristDetail from '../views/admin/Tourist/Detail.vue'
import TouristAdd from '../views/admin/Tourist/Add.vue'
import TouristEdit from '../views/admin/Tourist/Edit.vue'
import AdminAdd from '../views/admin/Admin/Add.vue'
import AdminEdit from '../views/admin/Admin/Edit.vue'
import AttractionInformation from '../views/admin/Attractions.vue'
import AttractionAdd from '../views/admin/Attractions/Add.vue'
import AttractionEdit from '../views/admin/Attractions/Edit.vue'
import ActivityInformation from '../views/admin/Activities.vue'
import ActivityAdd from '../views/admin/Activities/Add.vue'
import ActivityEdit from '../views/admin/Activities/Edit.vue'
import AccommodationInformation from '../views/admin/Accommodations.vue'
import AccommodationAdd from '../views/admin/Accommodations/Add.vue'
import AccommodationEdit from '../views/admin/Accommodations/Edit.vue'
import ArrivalInformation from '../views/admin/Arrivals/Index.vue'
import ArrivalAdd from '../views/admin/Arrivals/Add.vue'
import ArrivalEdit from '../views/admin/Arrivals/Edit.vue'
import Comments from '../views/admin/Comments.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminLogin,
      meta: { requiresGuest: true }
    },
    {
      path: '/admin/dashboard',
      name: 'admin/dashboard',
      component: AdminDashboard,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/management',
      name: 'admin/management',
      component: AdminManagement,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/add',
      name: 'admin/add',
      component: AdminAdd,
      meta: { requiresAuth: true }
    },
    // Tourist routes
    {
      path: '/admin/tourist-information',
      name: 'admin/tourist-information',
      component: TouristInformation,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/tourists/add',
      name: 'admin/tourist-add',
      component: TouristAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/tourists/:id',
      name: 'admin/tourist-detail',
      component: TouristDetail,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/tourists/:id/edit',
      name: 'admin/tourist-edit',
      component: TouristEdit,
      meta: { requiresAuth: true }
    },
    // Attraction routes
    {
      path: '/admin/attractions',
      name: 'admin/attractions',
      component: AttractionInformation,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/attractions/add',
      name: 'admin/attractions/add',
      component: AttractionAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/attractions/:id',
      name: 'admin/attractions/:id',
      component: AttractionEdit,
      meta: { requiresAuth: true }
    },
    // Activity routes
    {
      path: '/admin/activities',
      name: 'admin/activities',
      component: ActivityInformation,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/activities/add',
      name: 'admin/activities/add',
      component: ActivityAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/activities/:id',
      name: 'admin/activities/:id',
      component: ActivityEdit,
      meta: { requiresAuth: true }
    },
    // Accommodation routes
    {
      path: '/admin/accommodations',
      name: 'admin/accommodations',
      component: AccommodationInformation,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/accommodations/add',
      name: 'admin/accommodations/add',
      component: AccommodationAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/accommodations/:id',
      name: 'admin/accommodations/:id',
      component: AccommodationEdit,
      meta: { requiresAuth: true }
    },
    // Arrival routes
    {
      path: '/admin/arrivals',
      name: 'admin/arrivals',
      component: ArrivalInformation,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/arrivals/add',
      name: 'admin/arrivals/add',
      component: ArrivalAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/arrivals/:id/edit',
      name: 'admin/arrivals/:id/edit',
      component: ArrivalEdit,
      meta: { requiresAuth: true }
    },
    // Admin edit routes - must be last to avoid conflicts
    {
      path: '/admin/:id/edit',
      name: 'admin/edit',
      component: AdminEdit,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/:id',
      name: 'admin/view',
      component: AdminEdit,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/comments',
      name: 'AdminComments',
      component: Comments,
      meta: {
        requiresAuth: true,
        requiresAdmin: true
      }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  
  // Routes that require authentication
  if (to.meta.requiresAuth && !auth.isAdminAuthenticated) {
    next('/admin')
  }
  // Routes that require guest (like login page)
  else if (to.meta.requiresGuest && auth.isAdminAuthenticated) {
    next('/admin/dashboard')
  }
  else {
    next()
  }
})

export default router
