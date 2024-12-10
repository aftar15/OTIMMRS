<template>
  <div class="preference-manager">
    <div class="card">
      <div class="card-header">
        <h2 class="text-xl font-semibold">Your Travel Preferences</h2>
      </div>
      <div class="card-body">
        <!-- Interests Section -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-3">Interests</h3>
          <div class="flex flex-wrap gap-2">
            <div v-for="(interest, index) in availableInterests" :key="index"
              @click="toggleInterest(interest)"
              :class="['cursor-pointer px-3 py-1 rounded-full text-sm', 
                selectedInterests.includes(interest) 
                  ? 'bg-primary text-white' 
                  : 'bg-gray-100 hover:bg-gray-200']">
              {{ interest }}
            </div>
          </div>
        </div>

        <!-- Categories Section -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-3">Preferred Categories</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div v-for="(category, index) in availableCategories" :key="index"
              class="flex items-center">
              <input type="checkbox"
                :id="'category-' + index"
                v-model="selectedCategories"
                :value="category"
                class="form-checkbox h-4 w-4 text-primary">
              <label :for="'category-' + index" class="ml-2">{{ category }}</label>
            </div>
          </div>
        </div>

        <!-- Locations Section -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-3">Preferred Locations</h3>
          <div class="flex flex-wrap gap-2">
            <div v-for="(location, index) in selectedLocations" :key="index"
              class="bg-gray-100 px-3 py-1 rounded-full text-sm flex items-center">
              {{ location }}
              <button @click="removeLocation(index)" class="ml-2 text-gray-500 hover:text-red-500">
                ×
              </button>
            </div>
            <div class="w-full mt-2">
              <input type="text"
                v-model="newLocation"
                @keyup.enter="addLocation"
                placeholder="Add a location"
                class="form-input w-full rounded-md">
            </div>
          </div>
        </div>

        <!-- Price Range Section -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-3">Price Range</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Minimum</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">₱</span>
                </div>
                <input type="number"
                  v-model="priceRange.min"
                  class="form-input block w-full pl-7 pr-12 rounded-md"
                  placeholder="0">
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Maximum</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">₱</span>
                </div>
                <input type="number"
                  v-model="priceRange.max"
                  class="form-input block w-full pl-7 pr-12 rounded-md"
                  placeholder="5000">
              </div>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
          <button @click="savePreferences"
            class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark transition-colors"
            :disabled="isSaving">
            {{ isSaving ? 'Saving...' : 'Save Preferences' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'PreferenceManager',
  setup() {
    const availableInterests = ref([
      'Nature', 'Culture', 'Adventure', 'Food', 'History',
      'Art', 'Shopping', 'Nightlife', 'Beach', 'Mountains',
      'Wildlife', 'Photography', 'Sports', 'Relaxation'
    ])

    const availableCategories = ref([
      'Landmarks', 'Museums', 'Parks', 'Restaurants',
      'Hotels', 'Tours', 'Events', 'Activities',
      'Transportation', 'Entertainment'
    ])

    const selectedInterests = ref([])
    const selectedCategories = ref([])
    const selectedLocations = ref([])
    const newLocation = ref('')
    const priceRange = ref({ min: 0, max: 5000 })
    const isSaving = ref(false)

    const loadPreferences = async () => {
      try {
        const response = await axios.get('/api/recommendations/preferences')
        const preferences = response.data.data

        selectedInterests.value = preferences.interests || []
        selectedCategories.value = preferences.preferred_categories || []
        selectedLocations.value = preferences.preferred_locations || []
        priceRange.value = {
          min: preferences.preferred_price_range_min || 0,
          max: preferences.preferred_price_range_max || 5000
        }
      } catch (error) {
        console.error('Error loading preferences:', error)
      }
    }

    const toggleInterest = (interest) => {
      const index = selectedInterests.value.indexOf(interest)
      if (index === -1) {
        selectedInterests.value.push(interest)
      } else {
        selectedInterests.value.splice(index, 1)
      }
    }

    const addLocation = () => {
      if (newLocation.value.trim() && !selectedLocations.value.includes(newLocation.value.trim())) {
        selectedLocations.value.push(newLocation.value.trim())
        newLocation.value = ''
      }
    }

    const removeLocation = (index) => {
      selectedLocations.value.splice(index, 1)
    }

    const savePreferences = async () => {
      isSaving.value = true
      try {
        await axios.post('/api/recommendations/preferences', {
          interests: selectedInterests.value,
          preferred_categories: selectedCategories.value,
          preferred_locations: selectedLocations.value,
          preferred_price_range_min: priceRange.value.min,
          preferred_price_range_max: priceRange.value.max
        })
        // Show success notification
      } catch (error) {
        console.error('Error saving preferences:', error)
        // Show error notification
      } finally {
        isSaving.value = false
      }
    }

    onMounted(() => {
      loadPreferences()
    })

    return {
      availableInterests,
      availableCategories,
      selectedInterests,
      selectedCategories,
      selectedLocations,
      newLocation,
      priceRange,
      isSaving,
      toggleInterest,
      addLocation,
      removeLocation,
      savePreferences
    }
  }
}
</script>

<style scoped>
.preference-manager {
  @apply max-w-3xl mx-auto p-4;
}

.card {
  @apply bg-white rounded-lg shadow-md overflow-hidden;
}

.card-header {
  @apply bg-gray-50 px-6 py-4 border-b border-gray-200;
}

.card-body {
  @apply p-6;
}

.form-checkbox {
  @apply rounded border-gray-300 text-primary focus:ring-primary;
}

.form-input {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50;
}
</style>
