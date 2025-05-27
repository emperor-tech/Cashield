<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4">
      <!-- Active Incidents Panel -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
        <h2 class="text-xl font-bold mb-4">Active Incidents</h2>
        <div class="space-y-4">
          <div v-for="incident in activeIncidents" :key="incident.id"
               class="border-l-4 p-4 rounded-lg shadow bg-white dark:bg-gray-700"
               :class="getSeverityBorderClass(incident.severity)">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold">{{ incident.type }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ incident.description }}</p>
                <div class="flex items-center space-x-2 mt-2">
                  <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">
                    {{ incident.location }}
                  </span>
                  <span class="text-xs text-gray-500">
                    {{ formatTime(incident.created_at) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button @click="assignResponse(incident)" 
                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
                        v-if="!incident.has_response">
                  Respond
                </button>
                <button @click="viewDetails(incident)" 
                        class="bg-gray-200 dark:bg-gray-600 px-3 py-1 rounded text-sm">
                  Details
                </button>
              </div>
            </div>

            <!-- Response Teams (if assigned) -->
            <div v-if="incident.responses && incident.responses.length" class="mt-3">
              <div v-for="response in incident.responses" :key="response.id"
                   class="flex items-center justify-between bg-gray-50 dark:bg-gray-600 p-2 rounded mt-2">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 rounded-full" 
                       :class="getStatusColor(response.status)"></div>
                  <span class="text-sm">{{ response.responder.name }}</span>
                  <span class="text-xs text-gray-500">ETA: {{ response.eta_minutes }}m</span>
                </div>
                <span class="text-xs font-medium" :class="getStatusTextColor(response.status)">
                  {{ formatStatus(response.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Map and Resources Panel -->
      <div class="space-y-4">
        <!-- Campus Map -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-semibold mb-3">Campus Map</h3>
          <div class="h-96 relative">
            <l-map :zoom="16" :center="campusCenter">
              <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
              <!-- Incident Markers -->
              <l-marker v-for="incident in activeIncidents" 
                       :key="incident.id"
                       :lat-lng="[incident.location_lat, incident.location_lng]">
                <l-popup>
                  <div class="p-2">
                    <h4 class="font-medium">{{ incident.type }}</h4>
                    <p class="text-sm">{{ incident.description }}</p>
                    <button @click="viewDetails(incident)" 
                            class="mt-2 text-blue-500 text-sm">View Details</button>
                  </div>
                </l-popup>
              </l-marker>
              <!-- Response Team Markers -->
              <l-marker v-for="team in activeResponseTeams" 
                       :key="team.id"
                       :lat-lng="[team.location_lat, team.location_lng]"
                       :icon="responseTeamIcon">
                <l-popup>
                  <div class="p-2">
                    <h4 class="font-medium">{{ team.responder.name }}</h4>
                    <p class="text-sm">Status: {{ formatStatus(team.status) }}</p>
                  </div>
                </l-popup>
              </l-marker>
            </l-map>
          </div>
        </div>

        <!-- Available Resources -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-semibold mb-3">Available Resources</h3>
          <div class="space-y-3">
            <div v-for="resource in availableResources" 
                 :key="resource.id"
                 class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
              <div>
                <span class="font-medium">{{ resource.name }}</span>
                <p class="text-sm text-gray-500">{{ resource.location }}</p>
              </div>
              <div class="text-sm">
                <span class="px-2 py-1 rounded" 
                      :class="resource.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ resource.available ? 'Available' : 'In Use' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Response Assignment Modal -->
    <div v-if="showAssignModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Assign Response Team</h3>
        <form @submit.prevent="submitResponse" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Response Team</label>
            <select v-model="responseForm.team_id" class="w-full rounded-md border-gray-300">
              <option v-for="team in availableTeams" 
                      :key="team.id" 
                      :value="team.id">
                {{ team.name }} - {{ team.location }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">ETA (minutes)</label>
            <input type="number" 
                   v-model="responseForm.eta_minutes"
                   class="w-full rounded-md border-gray-300"
                   min="1" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Initial Action</label>
            <textarea v-model="responseForm.action_taken"
                      class="w-full rounded-md border-gray-300"
                      rows="3"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Required Resources</label>
            <div class="space-y-2">
              <div v-for="resource in availableResources" 
                   :key="resource.id"
                   class="flex items-center space-x-2">
                <input type="checkbox" 
                       :value="resource.id"
                       v-model="responseForm.resources" />
                <span>{{ resource.name }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button"
                    @click="showAssignModal = false"
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md">
              Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md">
              Dispatch Team
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'

export default {
  components: {
    LMap,
    LTileLayer,
    LMarker,
    LPopup
  },

  setup() {
    const activeIncidents = ref([])
    const activeResponseTeams = ref([])
    const availableResources = ref([])
    const availableTeams = ref([])
    const showAssignModal = ref(false)
    const selectedIncident = ref(null)
    const responseForm = ref({
      team_id: null,
      eta_minutes: 5,
      action_taken: '',
      resources: []
    })

    const campusCenter = ref([9.5836, 6.5244]) // AFIT coordinates

    const responseTeamIcon = {
      iconUrl: '/images/response-team-marker.png',
      iconSize: [32, 32],
      iconAnchor: [16, 32]
    }

    const getSeverityBorderClass = (severity) => ({
      'border-red-500': severity === 'high',
      'border-yellow-500': severity === 'medium',
      'border-blue-500': severity === 'low'
    })

    const getStatusColor = (status) => ({
      'bg-yellow-500': status === 'en_route',
      'bg-blue-500': status === 'on_scene',
      'bg-green-500': status === 'resolved',
      'bg-red-500': status === 'withdrawn'
    })

    const getStatusTextColor = (status) => ({
      'text-yellow-600': status === 'en_route',
      'text-blue-600': status === 'on_scene',
      'text-green-600': status === 'resolved',
      'text-red-600': status === 'withdrawn'
    })

    const formatStatus = (status) => {
      return status.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
      ).join(' ')
    }

    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleTimeString()
    }

    const assignResponse = (incident) => {
      selectedIncident.value = incident
      showAssignModal.value = true
    }

    const submitResponse = async () => {
      try {
        const response = await axios.post(
          `/reports/${selectedIncident.value.id}/respond`,
          responseForm.value
        )
        showAssignModal.value = false
        // Reset form
        responseForm.value = {
          team_id: null,
          eta_minutes: 5,
          action_taken: '',
          resources: []
        }
        // Refresh incidents
        await fetchActiveIncidents()
      } catch (error) {
        console.error('Failed to assign response:', error)
      }
    }

    const viewDetails = (incident) => {
      window.location.href = `/reports/${incident.id}`
    }

    const fetchActiveIncidents = async () => {
      try {
        const response = await axios.get('/api/active-incidents')
        activeIncidents.value = response.data.incidents
      } catch (error) {
        console.error('Failed to fetch incidents:', error)
      }
    }

    const fetchResources = async () => {
      try {
        const response = await axios.get('/api/resources')
        availableResources.value = response.data.resources
      } catch (error) {
        console.error('Failed to fetch resources:', error)
      }
    }

    onMounted(async () => {
      await Promise.all([
        fetchActiveIncidents(),
        fetchResources()
      ])

      // Listen for real-time updates
      window.Echo.channel('campus.security')
        .listen('ReportCreated', (e) => {
          activeIncidents.value = [e.report, ...activeIncidents.value]
        })
        .listen('EmergencyResponseUpdated', (e) => {
          // Update the corresponding incident's response information
          const incidentIndex = activeIncidents.value.findIndex(
            i => i.id === e.response.report_id
          )
          if (incidentIndex !== -1) {
            const incident = activeIncidents.value[incidentIndex]
            incident.responses = incident.responses || []
            const responseIndex = incident.responses.findIndex(
              r => r.id === e.response.id
            )
            if (responseIndex !== -1) {
              incident.responses[responseIndex] = e.response
            } else {
              incident.responses.push(e.response)
            }
            activeIncidents.value[incidentIndex] = { ...incident }
          }
        })
    })

    return {
      activeIncidents,
      activeResponseTeams,
      availableResources,
      availableTeams,
      showAssignModal,
      responseForm,
      campusCenter,
      responseTeamIcon,
      getSeverityBorderClass,
      getStatusColor,
      getStatusTextColor,
      formatStatus,
      formatTime,
      assignResponse,
      submitResponse,
      viewDetails
    }
  }
}
</script>