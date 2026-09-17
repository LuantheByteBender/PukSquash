<template>
  <div class="p-8 max-w-7xl mx-auto space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-200">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Coach Availability Notice Board</h1>
        <p class="text-sm text-gray-500 mt-1">Browse active squash coaches and check available training slots.</p>
      </div>
      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span> Live Schedule
      </span>
    </div>

    <!-- Filter Control Bar (Date & Time Picker) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <div>
        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Select Date</label>
        <input 
          v-model="selectedDate" 
          type="date" 
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"
        />
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Filter Time Slot</label>
        <select 
          v-model="selectedTimeSlot" 
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white transition-shadow"
        >
          <option value="ALL">All Available Times</option>
          <option value="08:00 - 09:00">08:00 - 09:00</option>
          <option value="09:00 - 10:00">09:00 - 10:00</option>
          <option value="10:00 - 11:00">10:00 - 11:00</option>
          <option value="14:00 - 15:00">14:00 - 15:00</option>
          <option value="15:00 - 16:00">15:00 - 16:00</option>
          <option value="16:00 - 17:00">16:00 - 17:00</option>
        </select>
      </div>

      <div>
        <button 
          @click="resetFilters" 
          class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          Reset Filters
        </button>
      </div>
    </div>

    <!-- View Coaches by Date + Available Times Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">
          Available Coaches ({{ formattedSelectedDate }})
        </h2>
        <span class="text-xs text-gray-500">{{ filteredCoaches.length }} Coaches Available</span>
      </div>

      <!-- Coach Cards Grid -->
      <div v-if="filteredCoaches.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="coach in filteredCoaches" 
          :key="coach.id" 
          class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
        >
          <div>
            <!-- Coach Header -->
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-base font-bold text-gray-900">{{ coach.name }}</h3>
                <p class="text-xs text-gray-500">{{ coach.specialization }}</p>
              </div>
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                Active
              </span>
            </div>

            <!-- Available Time Slots Badges -->
            <div class="mb-6">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Published Slots:</p>
              <div class="flex flex-wrap gap-1.5">
                <span 
                  v-for="time in coach.availableSlots" 
                  :key="time"
                  :class="selectedTimeSlot === time ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                  class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer"
                  @click="selectedTimeSlot = time"
                >
                  {{ time }}
                </span>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <button 
            @click="selectCoachForBooking(coach)"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
          >
            Book Private Session
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <p class="text-gray-500 text-sm">No coaches are available for the selected date and time slot.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Default to today's date (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0]
const selectedDate = ref(today)
const selectedTimeSlot = ref('ALL')

// Formatted Date string display
const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  const dateObj = new Date(selectedDate.value)
  return dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
})

// Reactive Sample Data representing published coach slots
const coaches = ref([
  {
    id: 101,
    name: 'Coach Sarah Jenkins',
    specialization: 'Advanced Technique & Strategy',
    availableSlots: ['08:00 - 09:00', '10:00 - 11:00', '15:00 - 16:00']
  },
  {
    id: 102,
    name: 'Coach Michael Vane',
    specialization: 'Beginner & Intermediate Drills',
    availableSlots: ['09:00 - 10:00', '14:00 - 15:00', '16:00 - 17:00']
  },
  {
    id: 103,
    name: 'Coach David Ross',
    specialization: 'Fitness & Match Conditioning',
    availableSlots: ['08:00 - 09:00', '15:00 - 16:00']
  }
])

// Filter logic matching Date + Time slot selection
const filteredCoaches = computed(() => {
  return coaches.value.filter(coach => {
    if (selectedTimeSlot.value === 'ALL') return true
    return coach.availableSlots.includes(selectedTimeSlot.value)
  })
})

function resetFilters() {
  selectedDate.value = today
  selectedTimeSlot.value = 'ALL'
}

function selectCoachForBooking(coach) {
  alert(`Proceeding to Private Coach Session booking for ${coach.name} on ${selectedDate.value}`)
}
</script>