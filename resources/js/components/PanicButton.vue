<template>
  <div class="fixed bottom-4 right-4 z-50">
    <!-- Panic Button -->
    <div v-if="!isActive" @click="activatePanic" 
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
    let countdownInterval
    let watchPositionId
    let currentPosition = null

    const formatTime = (seconds) => {
      const minutes = Math.floor(seconds / 60)
      const remainingSeconds = seconds % 60
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
    }

    const activatePanic = async () => {
      isActive.value = true
      startCountdown()
      await startLocationTracking()
      emit('panic-activated', { location: currentPosition })
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
      formatTime,
      activatePanic,
      cancelPanic,
      toggleFlashlight,
      playAlarm
    }
  }
}
</script>