<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold mb-6">Notification Preferences</h2>

    <form @submit.prevent="savePreferences" class="space-y-6">
      <!-- Enable/Disable Toggle -->
      <div class="flex items-center justify-between">
        <label class="text-lg">Enable Notifications</label>
        <button 
          type="button"
          @click="preferences.enabled = !preferences.enabled"
          :class="[
            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
            preferences.enabled ? 'bg-blue-600' : 'bg-gray-300'
          ]">
          <span
            :class="[
              'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
              preferences.enabled ? 'translate-x-6' : 'translate-x-1'
            ]"
          />
        </button>
      </div>

      <!-- Notification Methods -->
      <div>
        <label class="block text-sm font-medium mb-2">Notification Methods</label>
        <div class="space-y-2">
          <label class="flex items-center space-x-2">
            <input
              type="checkbox"
              v-model="preferences.notification_methods"
              value="web"
              class="rounded border-gray-300"
            />
            <span>Web Push Notifications</span>
          </label>
          <label class="flex items-center space-x-2">
            <input
              type="checkbox"
              v-model="preferences.notification_methods"
              value="email"
              class="rounded border-gray-300"
            />
            <span>Email Notifications</span>
          </label>
          <label class="flex items-center space-x-2">
            <input
              type="checkbox"
              v-model="preferences.notification_methods"
              value="sms"
              class="rounded border-gray-300"
            />
            <span>SMS Notifications</span>
          </label>
        </div>
      </div>

      <!-- Incident Types -->
      <div>
        <label class="block text-sm font-medium mb-2">Incident Types</label>
        <select 
          v-model="preferences.incident_type"
          class="w-full rounded-md border-gray-300">
          <option value="all">All Incidents</option>
          <option value="theft">Theft</option>
          <option value="assault">Assault</option>
          <option value="suspicious">Suspicious Activity</option>
          <option value="vandalism">Vandalism</option>
          <option value="fire">Fire</option>
          <option value="medical">Medical Emergency</option>
        </select>
      </div>

      <!-- Severity Level -->
      <div>
        <label class="block text-sm font-medium mb-2">Minimum Severity Level</label>
        <select 
          v-model="preferences.severity_level"
          class="w-full rounded-md border-gray-300">
          <option value="all">All Severities</option>
          <option value="low">Low and Above</option>
          <option value="medium">Medium and Above</option>
          <option value="high">High Only</option>
        </select>
      </div>

      <!-- Area Radius -->
      <div>
        <label class="block text-sm font-medium mb-2">
          Notification Area Radius (km)
        </label>
        <div class="flex items-center space-x-4">
          <input
            type="range"
            v-model.number="preferences.area_radius"
            min="0.5"
            max="5"
            step="0.5"
            class="w-full"
          />
          <span class="w-12 text-center">{{ preferences.area_radius }}km</span>
        </div>
      </div>

      <!-- Quiet Hours -->
      <div>
        <label class="block text-sm font-medium mb-2">Quiet Hours</label>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Start Time</label>
            <input
              type="time"
              v-model="preferences.quiet_hours_start"
              class="w-full rounded-md border-gray-300"
            />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">End Time</label>
            <input
              type="time"
              v-model="preferences.quiet_hours_end"
              class="w-full rounded-md border-gray-300"
            />
          </div>
        </div>
      </div>

      <!-- Preview Map -->
      <div v-if="preferences.area_radius">
        <label class="block text-sm font-medium mb-2">Notification Area</label>
        <div class="h-64 relative rounded-lg overflow-hidden">
          <l-map
            ref="map"
            :zoom="15"
            :center="userLocation"
            @ready="onMapReady"
          >
            <l-tile-layer
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <l-circle
              :lat-lng="userLocation"
              :radius="preferences.area_radius * 1000"
              color="blue"
              fill
              :fillOpacity="0.2"
            />
            <l-marker :lat-lng="userLocation">
              <l-popup>Your Location</l-popup>
            </l-marker>
          </l-map>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
          :disabled="saving"
        >
          {{ saving ? 'Saving...' : 'Save Preferences' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { LMap, LTileLayer, LMarker, LCircle, LPopup } from '@vue-leaflet/vue-leaflet'

export default {
  components: {
    LMap,
    LTileLayer,
    LMarker,
    LCircle,
    LPopup
  },

  setup() {
    const preferences = ref({
      enabled: true,
      notification_methods: ['web', 'email'],
      incident_type: 'all',
      severity_level: 'all',
      area_radius: 1,
      quiet_hours_start: null,
      quiet_hours_end: null
    })

    const saving = ref(false)
    const map = ref(null)
    const userLocation = ref([9.5836, 6.5244]) // Default to AFIT coordinates

    const savePreferences = async () => {
      saving.value = true
      try {
        await axios.post('/api/notification-preferences', preferences.value)
        // Show success message
        showSuccessNotification('Preferences saved successfully')
      } catch (error) {
        console.error('Failed to save preferences:', error)
        // Show error message
        showErrorNotification('Failed to save preferences')
      } finally {
        saving.value = false
      }
    }

    const getUserLocation = () => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            userLocation.value = [
              position.coords.latitude,
              position.coords.longitude
            ]
            if (map.value) {
              map.value.setView(userLocation.value, 15)
            }
          },
          (error) => {
            console.error('Location error:', error)
          }
        )
      }
    }

    const onMapReady = () => {
      getUserLocation()
    }

    const showSuccessNotification = (message) => {
      // Implementation depends on your notification system
      console.log('Success:', message)
    }

    const showErrorNotification = (message) => {
      // Implementation depends on your notification system
      console.error('Error:', message)
    }

    onMounted(async () => {
      try {
        const response = await axios.get('/api/notification-preferences')
        preferences.value = {
          ...preferences.value,
          ...response.data.preferences
        }
      } catch (error) {
        console.error('Failed to load preferences:', error)
      }
    })

    return {
      preferences,
      saving,
      map,
      userLocation,
      savePreferences,
      onMapReady
    }
  }
}
</script>