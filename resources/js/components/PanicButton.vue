<template>
  <div class="fixed bottom-4 right-4 z-50">
    <!-- Error Modal -->
    <div v-if="showError" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                  Error Sending Alert
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ errorMessage }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="hideErrorModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccess" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                  Emergency Alert Sent
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ successMessage }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="hideSuccessModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Panic Button -->
    <div v-if="!isActive" @click.prevent="activatePanic" 
         class="bg-red-600 hover:bg-red-700 text-white rounded-full w-16 h-16 flex items-center justify-center cursor-pointer shadow-lg transform hover:scale-105 transition-transform">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
      </svg>
    </div>

    <!-- Active Panic Mode -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 w-80">
      <div class="space-y-4">
        <!-- Countdown Timer -->
        <div class="text-center">
          <div class="text-3xl font-bold text-red-600">{{ formatTime(countdown) }}</div>
          <p class="text-sm text-gray-600 dark:text-gray-300">Help is on the way</p>
        </div>

        <!-- Location Status -->
        <div class="text-sm">
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ locationStatus }}</span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 gap-2">
          <button @click="toggleFlashlight" 
                  class="bg-blue-100 dark:bg-blue-900 p-2 rounded-lg text-sm flex items-center justify-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span>{{ flashlightActive ? 'Turn Off' : 'Turn On' }} Light</span>
          </button>

          <button @click="playAlarm" 
                  class="bg-red-100 dark:bg-red-900 p-2 rounded-lg text-sm flex items-center justify-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span>{{ alarmActive ? 'Stop' : 'Sound' }} Alarm</span>
          </button>
        </div>

        <!-- Cancel Button -->
        <button @click="cancelPanic" 
                class="w-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-2 rounded-lg text-sm">
          Cancel Emergency
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  props: {
    initialCountdown: {
      type: Number,
      default: 300 // 5 minutes
    }
  },

  setup(props, { emit }) {
    const isActive = ref(false)
    const countdown = ref(props.initialCountdown)
    const locationStatus = ref('Getting location...')
    const flashlightActive = ref(false)
    const alarmActive = ref(false)
    const showError = ref(false)
    const showSuccess = ref(false)
    const errorMessage = ref('')
    const successMessage = ref('')
    let countdownInterval
    let watchPositionId
    let currentPosition = null

    const formatTime = (seconds) => {
      const minutes = Math.floor(seconds / 60)
      const remainingSeconds = seconds % 60
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
    }

    const activatePanic = async (event) => {
      if (event) {
        event.preventDefault();
      }
      
      try {
        isActive.value = true;
        startCountdown();
        await startLocationTracking();
        
        const response = await fetch('/api/panic', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            latitude: currentPosition?.lat,
            longitude: currentPosition?.lng
          })
        });

        const data = await response.json();
        
        if (!data.success) {
          throw new Error(data.message || 'Failed to send emergency alert');
        }

        successMessage.value = data.message;
        showSuccess.value = true;
        emit('panic-activated', { location: currentPosition });
        
        // Redirect to the report view page after 2 seconds
        setTimeout(() => {
          window.location.href = `/reports/${data.report_id}`;
        }, 2000);
      } catch (error) {
        console.error('Panic activation error:', error);
        errorMessage.value = error.message || 'Failed to send emergency alert. Please try again or contact security directly.';
        showError.value = true;
        isActive.value = false;
        stopCountdown();
        stopLocationTracking();
      }
    }

    const hideSuccessModal = () => {
      showSuccess.value = false;
    }

    const hideErrorModal = () => {
      showError.value = false;
    }

    const cancelPanic = () => {
      isActive.value = false
      stopCountdown()
      stopLocationTracking()
      if (flashlightActive.value) toggleFlashlight()
      if (alarmActive.value) playAlarm()
      emit('panic-cancelled')
    }

    const startCountdown = () => {
      countdownInterval = setInterval(() => {
        if (countdown.value > 0) {
          countdown.value--
        } else {
          stopCountdown()
        }
      }, 1000)
    }

    const stopCountdown = () => {
      if (countdownInterval) {
        clearInterval(countdownInterval)
      }
    }

    const startLocationTracking = async () => {
      try {
        const position = await getCurrentPosition()
        currentPosition = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          accuracy: position.coords.accuracy
        }
        locationStatus.value = 'Location found'
        
        // Start watching position
        watchPositionId = navigator.geolocation.watchPosition(
          (position) => {
            currentPosition = {
              lat: position.coords.latitude,
              lng: position.coords.longitude,
              accuracy: position.coords.accuracy
            }
            emit('location-updated', currentPosition)
          },
          (error) => {
            locationStatus.value = 'Location error: ' + error.message
          },
          {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
          }
        )
      } catch (error) {
        locationStatus.value = 'Location error: ' + error.message
      }
    }

    const getCurrentPosition = () => {
      return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
          enableHighAccuracy: true,
          timeout: 5000,
          maximumAge: 0
        })
      })
    }

    const stopLocationTracking = () => {
      if (watchPositionId) {
        navigator.geolocation.clearWatch(watchPositionId)
      }
    }

    const toggleFlashlight = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment' }
        })
        const track = stream.getVideoTracks()[0]
        const capabilities = track.getCapabilities()
        
        if (capabilities.torch) {
          await track.applyConstraints({
            advanced: [{ torch: !flashlightActive.value }]
          })
          flashlightActive.value = !flashlightActive.value
        }
      } catch (error) {
        console.error('Flashlight error:', error)
      }
    }

    const playAlarm = () => {
      alarmActive.value = !alarmActive.value
      if (alarmActive.value) {
        // Play a high-pitched emergency sound
        const context = new (window.AudioContext || window.webkitAudioContext)()
        const oscillator = context.createOscillator()
        const gainNode = context.createGain()
        
        oscillator.connect(gainNode)
        gainNode.connect(context.destination)
        
        oscillator.type = 'triangle'
        oscillator.frequency.setValueAtTime(880, context.currentTime)
        
        gainNode.gain.setValueAtTime(0, context.currentTime)
        gainNode.gain.linearRampToValueAtTime(1, context.currentTime + 0.01)
        
        oscillator.start(context.currentTime)
        oscillator.stop(context.currentTime + 0.2)
      }
    }

    onMounted(() => {
      // Check for required permissions
      navigator.permissions.query({ name: 'geolocation' })
    })

    onUnmounted(() => {
      stopCountdown()
      stopLocationTracking()
      if (flashlightActive.value) toggleFlashlight()
    })

    return {
      isActive,
      countdown,
      locationStatus,
      flashlightActive,
      alarmActive,
      showError,
      showSuccess,
      errorMessage,
      successMessage,
      formatTime,
      activatePanic,
      cancelPanic,
      toggleFlashlight,
      playAlarm,
      hideSuccessModal,
      hideErrorModal
    }
  }
}
</script>