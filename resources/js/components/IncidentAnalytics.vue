<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-4">
    <!-- Overview Stats -->
    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div v-for="(stat, key) in overviewStats" 
           :key="key"
           class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">{{ stat.label }}</h3>
        <p class="text-2xl font-bold mt-1" :class="stat.color">{{ stat.value }}</p>
        <p class="text-xs mt-2" :class="stat.trend >= 0 ? 'text-green-500' : 'text-red-500'">
          {{ Math.abs(stat.trend) }}% {{ stat.trend >= 0 ? '↑' : '↓' }} vs last month
        </p>
      </div>
    </div>

    <!-- Incident Type Distribution -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Incident Types</h3>
      <canvas ref="typeChart"></canvas>
    </div>

    <!-- Response Time Analysis -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Response Times by Severity</h3>
      <canvas ref="responseTimeChart"></canvas>
    </div>

    <!-- Time Pattern Heatmap -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Incident Time Patterns</h3>
      <div class="relative h-64">
        <canvas ref="timePatternChart"></canvas>
      </div>
    </div>

    <!-- High Risk Areas -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
      <h3 class="text-lg font-semibold mb-4">High Risk Areas</h3>
      <div class="h-64">
        <l-map ref="riskMap" :zoom="16" :center="campusCenter">
          <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
          <l-circle-marker
            v-for="area in highRiskAreas"
            :key="area.location"
            :lat-lng="[area.coordinates.lat, area.coordinates.lng]"
            :radius="getRiskRadius(area.risk_score)"
            :color="getRiskColor(area.risk_score)"
            :fillColor="getRiskColor(area.risk_score)"
            :fillOpacity="0.6">
            <l-popup>
              <div class="p-2">
                <h4 class="font-medium">{{ area.location }}</h4>
                <p class="text-sm">Risk Score: {{ area.risk_score.toFixed(1) }}</p>
                <p class="text-xs mt-1">Common incidents:</p>
                <ul class="text-xs list-disc ml-4">
                  <li v-for="type in area.incident_types" :key="type">{{ type }}</li>
                </ul>
              </div>
            </l-popup>
          </l-circle-marker>
        </l-map>
      </div>
    </div>

    <!-- Filters -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium mb-1">Date Range</label>
          <select v-model="selectedRange" class="w-full rounded-md border-gray-300" @change="updateStats">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 3 months</option>
            <option value="365">Last year</option>
          </select>
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium mb-1">Incident Type</label>
          <select v-model="selectedType" class="w-full rounded-md border-gray-300" @change="updateStats">
            <option value="">All Types</option>
            <option v-for="type in incidentTypes" :key="type" :value="type">
              {{ type }}
            </option>
          </select>
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium mb-1">Location</label>
          <select v-model="selectedLocation" class="w-full rounded-md border-gray-300" @change="updateStats">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc" :value="loc">
              {{ loc }}
            </option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { LMap, LTileLayer, LCircleMarker, LPopup } from '@vue-leaflet/vue-leaflet'

Chart.register(...registerables)

export default {
  components: {
    LMap,
    LTileLayer,
    LCircleMarker,
    LPopup
  },

  setup() {
    const typeChart = ref(null)
    const responseTimeChart = ref(null)
    const timePatternChart = ref(null)
    const riskMap = ref(null)
    
    const selectedRange = ref('30')
    const selectedType = ref('')
    const selectedLocation = ref('')
    const campusCenter = ref([9.5836, 6.5244]) // AFIT coordinates

    const overviewStats = ref({})
    const highRiskAreas = ref([])
    const incidentTypes = ref([])
    const locations = ref([])

    const charts = {
      type: null,
      responseTime: null,
      timePattern: null
    }

    const updateStats = async () => {
      try {
        const response = await axios.get('/api/analytics', {
          params: {
            days: selectedRange.value,
            type: selectedType.value,
            location: selectedLocation.value
          }
        })

        overviewStats.value = response.data.overview
        highRiskAreas.value = response.data.high_risk_areas
        updateCharts(response.data)
      } catch (error) {
        console.error('Failed to fetch analytics:', error)
      }
    }

    const updateCharts = (data) => {
      // Update Type Distribution Chart
      if (charts.type) {
        charts.type.data.datasets[0].data = data.by_type.map(t => t.count)
        charts.type.data.labels = data.by_type.map(t => t.type)
        charts.type.update()
      }

      // Update Response Time Chart
      if (charts.responseTime) {
        charts.responseTime.data.datasets[0].data = Object.values(data.response_times)
        charts.responseTime.data.labels = Object.keys(data.response_times)
        charts.responseTime.update()
      }

      // Update Time Pattern Chart
      if (charts.timePattern) {
        const timeData = []
        for (let day = 0; day < 7; day++) {
          for (let hour = 0; hour < 24; hour++) {
            timeData.push({
              x: hour,
              y: day,
              v: (data.time_patterns[day + 1]?.[hour] || 0)
            })
          }
        }
        charts.timePattern.data.datasets[0].data = timeData
        charts.timePattern.update()
      }
    }

    const initCharts = () => {
      // Type Distribution Chart
      charts.type = new Chart(typeChart.value, {
        type: 'doughnut',
        data: {
          labels: [],
          datasets: [{
            data: [],
            backgroundColor: [
              '#2563eb', '#7c3aed', '#db2777', '#dc2626',
              '#ea580c', '#65a30d', '#0891b2', '#6366f1'
            ]
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'right'
            }
          }
        }
      })

      // Response Time Chart
      charts.responseTime = new Chart(responseTimeChart.value, {
        type: 'bar',
        data: {
          labels: [],
          datasets: [{
            label: 'Average Response Time (minutes)',
            data: [],
            backgroundColor: '#3b82f6'
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Minutes'
              }
            }
          }
        }
      })

      // Time Pattern Chart
      charts.timePattern = new Chart(timePatternChart.value, {
        type: 'scatter',
        data: {
          datasets: [{
            data: [],
            backgroundColor: (context) => {
              const value = context.raw?.v || 0
              return `rgba(239, 68, 68, ${Math.min(value / 10, 1)})`
            },
            pointRadius: 10
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: {
              min: 0,
              max: 23,
              title: {
                display: true,
                text: 'Hour of Day'
              }
            },
            y: {
              min: 0,
              max: 6,
              ticks: {
                callback: (value) => {
                  return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][value]
                }
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const value = context.raw.v
                  return `${value} incidents`
                }
              }
            }
          }
        }
      })
    }

    const getRiskRadius = (score) => {
      return Math.max(10, Math.min(30, score * 2))
    }

    const getRiskColor = (score) => {
      if (score > 8) return '#dc2626' // red
      if (score > 5) return '#ea580c' // orange
      return '#65a30d' // green
    }

    onMounted(() => {
      initCharts()
      updateStats()
    })

    watch([selectedRange, selectedType, selectedLocation], () => {
      updateStats()
    })

    return {
      typeChart,
      responseTimeChart,
      timePatternChart,
      riskMap,
      selectedRange,
      selectedType,
      selectedLocation,
      campusCenter,
      overviewStats,
      highRiskAreas,
      incidentTypes,
      locations,
      getRiskRadius,
      getRiskColor
    }
  }
}
</script>