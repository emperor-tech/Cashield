<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
    <div v-if="response" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">
          Response Status: 
          <span :class="statusClasses">{{ response.status }}</span>
        </h3>
        <div class="text-sm text-gray-500">
          ETA: {{ response.eta_minutes }} minutes
        </div>
      </div>

      <!-- Response Team Location -->
      <div v-if="response.location_lat && response.location_lng" class="h-64 relative">
        <l-map :zoom="15" :center="[response.location_lat, response.location_lng]">
          <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
          <l-marker :lat-lng="[response.location_lat, response.location_lng]">
            <l-popup>Response Team Location</l-popup>
          </l-marker>
          <l-marker v-if="report" :lat-lng="[report.location_lat, report.location_lng]">
            <l-popup>Incident Location</l-popup>
          </l-marker>
        </l-map>
      </div>

      <!-- Updates Timeline -->
      <div class="mt-4 space-y-4">
        <h4 class="font-medium">Response Updates</h4>
        <div class="space-y-3">
          <div v-for="update in response.updates" :key="update.id" 
               class="border-l-4 pl-4" 
               :class="getUpdateBorderClass(update.status)">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-medium">{{ update.status }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ update.message }}</p>
              </div>
              <span class="text-xs text-gray-500">
                {{ formatDate(update.created_at) }}
              </span>
            </div>
            <img v-if="update.image_path" 
                 :src="'/storage/' + update.image_path" 
                 class="mt-2 rounded-lg max-h-48 object-cover" />
          </div>
        </div>
      </div>

      <!-- Update Form for Responders -->
      <form v-if="isResponder" @submit.prevent="submitUpdate" class="mt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium">Status Update</label>
          <select v-model="updateForm.status" class="mt-1 block w-full rounded-md border-gray-300">
            <option value="en_route">En Route</option>
            <option value="on_scene">On Scene</option>
            <option value="resolved">Resolved</option>
            <option value="withdrawn">Withdrawn</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium">Message</label>
          <textarea v-model="updateForm.message" 
                    class="mt-1 block w-full rounded-md border-gray-300"
                    rows="3"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium">Photo</label>
          <input type="file" 
                 @change="handleImageUpload" 
                 accept="image/*"
                 class="mt-1 block w-full" />
        </div>

        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
          Submit Update
        </button>
      </form>
    </div>

    <div v-else class="text-center py-8">
      <p>No active response for this report</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import Echo from 'laravel-echo'

export default {
  components: {
    LMap,
    LTileLayer,
    LMarker,
    LPopup
  },

  props: {
    reportId: {
      type: Number,
      required: true
    },
    isResponder: {
      type: Boolean,
      default: false
    }
  },

  setup(props) {
    const response = ref(null)
    const report = ref(null)
    const updateForm = ref({
      status: 'en_route',
      message: '',
      image: null
    })

    const statusClasses = computed(() => ({
      'text-yellow-600': response.value?.status === 'en_route',
      'text-blue-600': response.value?.status === 'on_scene',
      'text-green-600': response.value?.status === 'resolved',
      'text-red-600': response.value?.status === 'withdrawn'
    }))

    const getUpdateBorderClass = (status) => {
      return {
        'border-yellow-500': status === 'en_route',
        'border-blue-500': status === 'on_scene',
        'border-green-500': status === 'resolved',
        'border-red-500': status === 'withdrawn'
      }
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleString()
    }

    const handleImageUpload = (event) => {
      updateForm.value.image = event.target.files[0]
    }

    const submitUpdate = async () => {
      const formData = new FormData()
      formData.append('status', updateForm.value.status)
      formData.append('message', updateForm.value.message)
      if (updateForm.value.image) {
        formData.append('image', updateForm.value.image)
      }

      try {
        const res = await axios.post(`/responses/${response.value.id}/update`, formData)
        updateForm.value.message = ''
        updateForm.value.image = null
      } catch (error) {
        console.error('Failed to submit update:', error)
      }
    }

    onMounted(async () => {
      try {
        const res = await axios.get(`/reports/${props.reportId}/responses`)
        if (res.data.responses.length > 0) {
          response.value = res.data.responses[0]
          report.value = response.value.report
        }

        // Listen for real-time updates
        window.Echo.private(`emergency.response.${props.reportId}`)
          .listen('EmergencyResponseUpdated', (e) => {
            response.value = e.response
          })
      } catch (error) {
        console.error('Failed to fetch response:', error)
      }
    })

    return {
      response,
      report,
      updateForm,
      statusClasses,
      getUpdateBorderClass,
      formatDate,
      handleImageUpload,
      submitUpdate
    }
  }
}
</script>