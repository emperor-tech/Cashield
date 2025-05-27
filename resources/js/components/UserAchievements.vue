<template>
  <div class="space-y-6">
    <!-- Points Overview -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold">{{ stats.total_points }}</h3>
          <p class="text-blue-100">Safety Points</p>
        </div>
        <div class="text-right">
          <p class="text-lg">🔥 {{ stats.contribution_streak }} Day Streak</p>
          <p class="text-sm text-blue-100">Keep it going!</p>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reports Submitted</p>
            <p class="text-2xl font-bold">{{ stats.reports_submitted }}</p>
          </div>
          <span class="text-2xl">📝</span>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Verified Reports</p>
            <p class="text-2xl font-bold">{{ stats.reports_verified }}</p>
          </div>
          <span class="text-2xl">✅</span>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Response Time</p>
            <p class="text-2xl font-bold">{{ formatResponseTime(stats.average_response_time) }}</p>
          </div>
          <span class="text-2xl">⚡</span>
        </div>
      </div>
    </div>

    <!-- Badges Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold mb-4">Earned Badges</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <div v-for="badge in user.badges" :key="badge.id"
             class="relative group">
          <div class="flex flex-col items-center p-4 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
            <span class="text-4xl mb-2">{{ badge.icon }}</span>
            <h4 class="font-medium text-sm text-center">{{ badge.name }}</h4>
            
            <!-- Tooltip -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg w-48 text-center">
              {{ badge.description }}
              <div class="absolute left-1/2 transform -translate-x-1/2 top-full w-2 h-2 bg-gray-900 rotate-45"></div>
            </div>
          </div>
        </div>

        <!-- Locked Badge Placeholders -->
        <div v-for="badge in lockedBadges" :key="badge.name"
             class="relative group">
          <div class="flex flex-col items-center p-4 rounded-lg bg-gray-100 dark:bg-gray-700 opacity-50">
            <span class="text-4xl mb-2 grayscale">{{ badge.icon }}</span>
            <h4 class="font-medium text-sm text-center">???</h4>
            
            <!-- Tooltip -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg w-48 text-center">
              {{ badge.description }}
              <div class="absolute left-1/2 transform -translate-x-1/2 top-full w-2 h-2 bg-gray-900 rotate-45"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Most Active Areas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold mb-4">Most Active Areas</h3>
      <div class="space-y-4">
        <div v-for="area in stats.most_active_areas" :key="area.location"
             class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <span class="font-medium">{{ area.location }}</span>
          </div>
          <span class="text-sm text-gray-500">{{ area.count }} reports</span>
        </div>
      </div>
    </div>

    <!-- Recent Achievements -->
    <div v-if="recentAchievements.length" 
         class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold mb-4">Recent Achievements</h3>
      <div class="space-y-4">
        <div v-for="achievement in recentAchievements" :key="achievement.id"
             class="flex items-center space-x-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
          <span class="text-2xl">{{ achievement.icon }}</span>
          <div>
            <h4 class="font-medium">{{ achievement.title }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ achievement.description }}</p>
          </div>
          <span class="ml-auto text-sm font-medium text-blue-600 dark:text-blue-400">
            +{{ achievement.points }} points
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  props: {
    user: {
      type: Object,
      required: true
    }
  },

  setup(props) {
    const stats = ref({
      total_points: 0,
      reports_submitted: 0,
      reports_verified: 0,
      emergency_responses: 0,
      average_response_time: 0,
      contribution_streak: 0,
      most_active_areas: []
    })

    const recentAchievements = ref([])
    const allBadges = [
      { name: 'First Report', icon: '🎯', description: 'Submit your first report' },
      { name: 'Vigilant Reporter', icon: '🥈', description: 'Submit 5 reports' },
      { name: 'Safety Expert', icon: '🥇', description: 'Submit 25 reports' },
      { name: 'Quick Responder', icon: '⚡', description: 'Respond within 5 minutes' },
      { name: 'Life Saver', icon: '💖', description: 'Handle a high-priority emergency' },
      { name: 'Night Watch', icon: '🌙', description: 'Report during night hours' },
      { name: 'Team Player', icon: '🤝', description: 'Collaborate on 10 responses' },
      { name: 'Accuracy Star', icon: '⭐', description: 'Get 10 reports verified' },
      { name: 'Campus Guardian', icon: '🛡️', description: 'Earn 1000 points' },
      { name: 'First Aid Hero', icon: '🏥', description: 'Handle 5 medical emergencies' }
    ]

    const formatResponseTime = (minutes) => {
      if (!minutes) return 'N/A'
      return `${Math.round(minutes)}m`
    }

    const getLockedBadges = () => {
      const earnedBadgeNames = props.user.badges.map(b => b.name)
      return allBadges.filter(b => !earnedBadgeNames.includes(b.name))
    }

    const fetchUserStats = async () => {
      try {
        const response = await axios.get(`/api/users/${props.user.id}/stats`)
        stats.value = response.data.stats
      } catch (error) {
        console.error('Failed to fetch user stats:', error)
      }
    }

    const fetchRecentAchievements = async () => {
      try {
        const response = await axios.get(`/api/users/${props.user.id}/achievements`)
        recentAchievements.value = response.data.achievements
      } catch (error) {
        console.error('Failed to fetch achievements:', error)
      }
    }

    onMounted(() => {
      fetchUserStats()
      fetchRecentAchievements()
    })

    return {
      stats,
      recentAchievements,
      formatResponseTime,
      lockedBadges: getLockedBadges()
    }
  }
}
</script>