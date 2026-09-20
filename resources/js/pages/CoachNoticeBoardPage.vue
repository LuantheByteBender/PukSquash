<template>
  <div class="p-8 max-w-7xl mx-auto space-y-8">
    <!-- Header Section -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-200"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
          Coach Availability Notice Board
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Browse active squash coaches and check available training slots.
        </p>
      </div>

      <span
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200"
      >
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Live Schedule
      </span>
    </div>

    <!-- Filter Controls -->
    <div
      class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4 items-end"
    >
      <!-- Date -->
      <div>
        <label
          class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2"
        >
          Select Date
        </label>

        <input
          v-model="selectedDate"
          type="date"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"
        />
      </div>

      <!-- Time Slot -->
      <div>
        <label
          class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2"
        >
          Filter Time Slot
        </label>

        <select
          v-model="selectedTimeSlot"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white transition-shadow"
        >
          <option value="ALL">All Available Times</option>

          <option
            v-for="time in availableTimeSlots"
            :key="time"
            :value="time"
          >
            {{ time }}
          </option>
        </select>
      </div>

      <!-- Reset -->
      <div>
        <button
          type="button"
          @click="resetFilters"
          class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Available Coaches -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">
          Available Coaches ({{ formattedSelectedDate }})
        </h2>

        <span class="text-xs text-gray-500">
          {{ filteredCoaches.length }} Coaches Available
        </span>
      </div>

      <!-- Loading State -->
      <div
        v-if="loading"
        class="bg-white border border-gray-200 rounded-xl p-12 text-center"
      >
        <p class="text-gray-500 text-sm">
          Loading coach availability...
        </p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="errorMessage"
        class="bg-white border border-red-200 rounded-xl p-6 text-center"
      >
        <p class="text-red-600 text-sm">
          {{ errorMessage }}
        </p>
      </div>

      <!-- Coach Cards -->
      <div
        v-else-if="filteredCoaches.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <div
          v-for="coach in filteredCoaches"
          :key="coach.id"
          class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
        >
          <div>
            <!-- Coach Header -->
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-base font-bold text-gray-900">
                  {{ coach.name }}
                </h3>

                <p class="text-xs text-gray-500">
                  {{ coach.specialization }}
                </p>
              </div>

              <span
                class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20"
              >
                Active
              </span>
            </div>

            <!-- Published Time Slots -->
            <div class="mb-6">
              <p
                class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2"
              >
                Published Slots:
              </p>

              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="time in coach.availableSlots"
                  :key="time"
                  :class="
                    selectedTimeSlot === time
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  "
                  class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer"
                  @click="selectedTimeSlot = time"
                >
                  {{ time }}
                </span>
              </div>
            </div>
          </div>

          <!-- Booking Button -->
          <button
            type="button"
            @click="selectCoachForBooking(coach)"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
          >
            Book Private Session
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="bg-white border border-gray-200 rounded-xl p-12 text-center"
      >
        <p class="text-gray-500 text-sm">
          No coaches are available for the selected date and time slot.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { http } from '../services/http.js'

// Default to today's date (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0]

const selectedDate = ref(today)
const selectedTimeSlot = ref('ALL')

const coaches = ref([])
const loading = ref(false)
const errorMessage = ref('')

// Display selected date in a readable format
const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) {
    return ''
  }

  const dateObj = new Date(`${selectedDate.value}T00:00:00`)

  return dateObj.toLocaleDateString('en-ZA', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
})

// Build the time-slot filter from the real Notice Board data
const availableTimeSlots = computed(() => {
  const slots = new Set()

  coaches.value.forEach(coach => {
    coach.availableSlots.forEach(slot => {
      slots.add(slot)
    })
  })

  return Array.from(slots)
})

// Filter coaches by selected time slot
const filteredCoaches = computed(() => {
  if (selectedTimeSlot.value === 'ALL') {
    return coaches.value
  }

  return coaches.value.filter(coach =>
    coach.availableSlots.includes(selectedTimeSlot.value)
  )
})

// Load published coach availability from Laravel
async function loadNoticeBoard() {
  if (!selectedDate.value) {
    coaches.value = []
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const result = await http.get(
      `/notice-board/coaches?date=${selectedDate.value}`
    )

    coaches.value = (result.data ?? []).map(coach => ({
      id: coach.coach_id,
      name: coach.coach_name,
      specialization: 'Squash Coach',

      availableSlots: (coach.time_slots ?? []).map(
        slot => `${slot.start_time} - ${slot.end_time}`
      )
    }))
  } catch (error) {
    coaches.value = []

    errorMessage.value =
      error.message || 'Unable to load coach availability.'
  } finally {
    loading.value = false
  }
}

// Reset filters to today
function resetFilters() {
  selectedDate.value = today
  selectedTimeSlot.value = 'ALL'
}

// Booking is not connected yet.
// Keep this temporary action until booking integration is implemented.
function selectCoachForBooking(coach) {
  alert(
    `Proceeding to Private Coach Session booking for ${coach.name} on ${selectedDate.value}`
  )
}

// Reload Notice Board whenever the selected date changes
watch(selectedDate, () => {
  selectedTimeSlot.value = 'ALL'
  loadNoticeBoard()
})

// Initial load
onMounted(() => {
  loadNoticeBoard()
})
</script>