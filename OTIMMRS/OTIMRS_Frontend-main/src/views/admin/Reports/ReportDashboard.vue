<template>
  <div class="report-dashboard">
    <div class="container mx-auto px-4 py-8">
      <!-- Header Section -->
      <div class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
          <p class="mt-2 text-gray-600">Comprehensive overview of system performance and metrics</p>
        </div>
        <div class="flex gap-4">
          <select v-model="timeframe" class="form-select rounded-md shadow-sm">
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="quarter">Last Quarter</option>
            <option value="year">Last Year</option>
          </select>
          <button @click="exportReport" class="btn-primary">
            Export Report
          </button>
        </div>
      </div>

      <!-- Overview Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div v-for="(stat, index) in overviewStats" :key="index"
          class="bg-white rounded-lg shadow p-6">
          <h3 class="text-gray-500 text-sm font-medium">{{ stat.label }}</h3>
          <p class="mt-2 text-3xl font-semibold text-gray-900">{{ stat.value }}</p>
          <p class="mt-2 text-sm" :class="stat.trend >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ stat.trend >= 0 ? '↑' : '↓' }} {{ Math.abs(stat.trend) }}% from last period
          </p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Tourist Activity Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Tourist Activity</h3>
          <LineChart
            :chart-data="touristActivityData"
            :options="chartOptions" />
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Revenue Analysis</h3>
          <BarChart
            :chart-data="revenueData"
            :options="chartOptions" />
        </div>
      </div>

      <!-- Detailed Stats Tabs -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'px-6 py-3 text-sm font-medium',
                activeTab === tab.key
                  ? 'border-b-2 border-primary text-primary'
                  : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]">
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Attractions Tab -->
          <div v-if="activeTab === 'attractions'" class="space-y-6">
            <h4 class="text-lg font-semibold">Most Popular Attractions</h4>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="attraction in popularAttractions" :key="attraction.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ attraction.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ attraction.visits }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="text-yellow-400">★</span>
                        <span class="ml-1">{{ attraction.rating }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="attraction.trend >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ attraction.trend >= 0 ? '↑' : '↓' }} {{ Math.abs(attraction.trend) }}%
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Activities Tab -->
          <div v-if="activeTab === 'activities'" class="space-y-6">
            <h4 class="text-lg font-semibold">Activity Performance</h4>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="activity in activityPerformance" :key="activity.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ activity.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ activity.bookings }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">₱{{ activity.revenue }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" :style="{ width: activity.utilization + '%' }"></div>
                      </div>
                      <span class="text-sm text-gray-600">{{ activity.utilization }}%</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tourists Tab -->
          <div v-if="activeTab === 'tourists'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Demographics Chart -->
              <div class="bg-white rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">Demographics</h4>
                <PieChart
                  :chart-data="demographicsData"
                  :options="pieChartOptions" />
              </div>

              <!-- Engagement Metrics -->
              <div class="bg-white rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">User Engagement</h4>
                <BarChart
                  :chart-data="engagementData"
                  :options="chartOptions" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line, Bar, Pie } from 'vue-chartjs'

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

export default {
  name: 'ReportDashboard',
  components: {
    LineChart: Line,
    BarChart: Bar,
    PieChart: Pie
  },
  setup() {
    const timeframe = ref('month')
    const activeTab = ref('attractions')
    const isLoading = ref(false)

    const tabs = [
      { key: 'attractions', label: 'Attractions' },
      { key: 'activities', label: 'Activities' },
      { key: 'tourists', label: 'Tourists' }
    ]

    const overviewStats = ref([])
    const popularAttractions = ref([])
    const activityPerformance = ref([])
    const touristActivityData = ref(null)
    const revenueData = ref(null)
    const demographicsData = ref(null)
    const engagementData = ref(null)

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false
    }

    const pieChartOptions = {
      ...chartOptions,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }

    const loadData = async () => {
      isLoading.value = true
      try {
        const [overview, attractions, activities, tourists] = await Promise.all([
          axios.get('/api/reports/overview'),
          axios.get(`/api/reports/attractions?timeframe=${timeframe.value}`),
          axios.get(`/api/reports/activities?timeframe=${timeframe.value}`),
          axios.get(`/api/reports/tourists?timeframe=${timeframe.value}`)
        ])

        // Process overview data
        overviewStats.value = processOverviewStats(overview.data.data)
        
        // Process attractions data
        popularAttractions.value = processAttractionStats(attractions.data.data)
        
        // Process activities data
        activityPerformance.value = processActivityStats(activities.data.data)
        
        // Process tourist data
        const touristStats = tourists.data.data
        touristActivityData.value = processTouristActivityData(touristStats)
        demographicsData.value = processDemographicsData(touristStats)
        engagementData.value = processEngagementData(touristStats)
        
        // Process revenue data
        revenueData.value = processRevenueData(activities.data.data)
      } catch (error) {
        console.error('Error loading report data:', error)
      } finally {
        isLoading.value = false
      }
    }

    const exportReport = async () => {
      try {
        const response = await axios.get(`/api/reports/export`, {
          params: {
            type: activeTab.value,
            timeframe: timeframe.value,
            format: 'csv'
          },
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `report_${activeTab.value}_${timeframe.value}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
      } catch (error) {
        console.error('Error exporting report:', error)
      }
    }

    // Data processing functions
    const processOverviewStats = (data) => {
      return [
        {
          label: 'Total Tourists',
          value: data.total_tourists,
          trend: calculateTrend(data.total_tourists, data.monthly_stats.new_tourists)
        },
        {
          label: 'Total Attractions',
          value: data.total_attractions,
          trend: 0 // Static number
        },
        {
          label: 'Total Activities',
          value: data.total_activities,
          trend: 0 // Static number
        },
        {
          label: 'Monthly Revenue',
          value: `₱${data.monthly_stats.revenue.toLocaleString()}`,
          trend: calculateTrend(data.monthly_stats.revenue, data.monthly_stats.revenue)
        }
      ]
    }

    const calculateTrend = (current, previous) => {
      if (!previous) return 0
      return ((current - previous) / previous * 100).toFixed(1)
    }

    // Watch for changes in timeframe or active tab
    watch([timeframe, activeTab], () => {
      loadData()
    })

    onMounted(() => {
      loadData()
    })

    return {
      timeframe,
      activeTab,
      tabs,
      isLoading,
      overviewStats,
      popularAttractions,
      activityPerformance,
      touristActivityData,
      revenueData,
      demographicsData,
      engagementData,
      chartOptions,
      pieChartOptions,
      exportReport
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark transition-colors;
}

.form-select {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50;
}
</style>
