<template>
  <div>
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
      <nav class="flex space-x-8" aria-label="Profile sections">
        <button @click="activeTab = 'profile'; updateHash('profile')"
                :class="{'border-b-2 border-blue-500': activeTab === 'profile'}"
                class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
          Profile Information
        </button>
        
        <button @click="activeTab = 'achievements'; updateHash('achievements')"
                :class="{'border-b-2 border-blue-500': activeTab === 'achievements'}"
                class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
          Achievements
        </button>
        
        <button @click="activeTab = 'notifications'; updateHash('notifications')"
                :class="{'border-b-2 border-blue-500': activeTab === 'notifications'}"
                class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
          Notifications
        </button>

        <button @click="activeTab = 'password'; updateHash('password')"
                :class="{'border-b-2 border-blue-500': activeTab === 'password'}"
                class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
          Password
        </button>
      </nav>
    </div>

    <!-- Tab Panels -->
    <div v-show="activeTab === 'profile'" class="profile-tab-panel">
      <div v-html="profileForm"></div>
    </div>

    <div v-show="activeTab === 'achievements'" class="profile-tab-panel max-w-4xl">
      <user-achievements :user="user"></user-achievements>
    </div>

    <div v-show="activeTab === 'notifications'" class="profile-tab-panel">
      <div v-html="notificationsForm"></div>
      <div id="notification-preferences"></div>
    </div>

    <div v-show="activeTab === 'password'" class="profile-tab-panel">
      <div v-html="passwordForm"></div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import UserAchievements from './UserAchievements.vue'
import NotificationPreferences from './NotificationPreferences.vue'

export default {
  components: {
    UserAchievements,
    NotificationPreferences
  },
  
  props: {
    user: {
      type: Object,
      required: true
    },
    profileForm: {
      type: String,
      default: ''
    },
    notificationsForm: {
      type: String,
      default: ''
    },
    passwordForm: {
      type: String,
      default: ''
    }
  },
  
  setup() {
    const activeTab = ref(window.location.hash ? window.location.hash.substring(1) : 'profile')
    
    const updateHash = (hash) => {
      window.location.hash = hash
    }
    
    onMounted(() => {
      console.log('ProfileTabs component mounted')
      
      // Mount NotificationPreferences component after the tab is shown
      const mountNotificationPreferences = () => {
        const notificationPreferencesEl = document.getElementById('notification-preferences')
        if (notificationPreferencesEl) {
          try {
            const app = Vue.createApp(NotificationPreferences)
            app.mount(notificationPreferencesEl)
            console.log('NotificationPreferences component mounted from ProfileTabs')
          } catch (error) {
            console.error('Error mounting NotificationPreferences component from ProfileTabs:', error)
          }
        }
      }
      
      // Watch for hash changes
      window.addEventListener('hashchange', () => {
        activeTab.value = window.location.hash.substring(1) || 'profile'
        if (activeTab.value === 'notifications') {
          setTimeout(mountNotificationPreferences, 100)
        }
      })
      
      // Mount NotificationPreferences if notifications tab is active
      if (activeTab.value === 'notifications') {
        setTimeout(mountNotificationPreferences, 100)
      }
    })
    
    return {
      activeTab,
      updateHash
    }
  }
}
</script>

<style scoped>
.profile-tab-panel {
  transition: opacity 0.3s ease;
}
</style>