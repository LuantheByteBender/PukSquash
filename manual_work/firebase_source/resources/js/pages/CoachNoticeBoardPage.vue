<template>
  <div class="p-6 sm:p-8 max-w-5xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap pb-5 border-b border-gray-200">
      <div>
        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Squash coaching</p>
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight mt-1">Coach Notice Board</h1>
        <p class="text-sm text-gray-500 mt-1">Pick a date, then expand a time slot to see which coaches are available.</p>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
      <button
        type="button"
        class="h-9 w-9 rounded-lg border border-gray-200 bg-gray-50 hover:bg-indigo-50 hover:border-indigo-200 text-gray-700 transition-colors"
        title="Previous day"
        @click="shiftDate(-1)"
      >
        &larr;
      </button>

      <div class="flex-1 min-w-0 flex items-center gap-3">
        <input
          v-model="selectedDate"
          type="date"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
        />
        <p class="text-sm font-semibold text-gray-700 truncate">{{ formattedSelectedDate }}</p>
      </div>

      <button
        type="button"
        class="h-9 w-9 rounded-lg border border-gray-200 bg-gray-50 hover:bg-indigo-50 hover:border-indigo-200 text-gray-700 transition-colors"
        title="Next day"
        @click="shiftDate(1)"
      >
        &rarr;
      </button>
    </div>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-600">Available coaches</h2>
        <span v-if="loading" class="text-xs text-gray-400">Refreshing…</span>
      </div>

      <div v-if="errorMessage" class="bg-white border border-red-200 rounded-xl p-4 text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <div v-else-if="!loading && coaches.length === 0" class="bg-white border border-gray-200 rounded-xl p-10 text-center">
        <p class="text-sm text-gray-500">No coaches have published availability for this date.</p>
      </div>

      <div v-else class="space-y-3">
        <div v-for="coach in coaches" :key="coach.id" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <button
            type="button"
            class="w-full px-4 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors"
            @click="toggleCoach(coach.id)"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div
                class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                :class="avatarClass(coach.id)"
              >
                {{ initials(coach.name) }}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">{{ coach.name || 'Coach' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ coach.time_slots.length }} slot{{ coach.time_slots.length !== 1 ? 's' : '' }}</p>
              </div>
            </div>

            <span
              class="text-xs text-gray-400 transition-transform"
              :class="isCoachExpanded(coach.id) ? 'rotate-180 text-indigo-600' : ''"
            >
              &#9662;
            </span>
          </button>

          <div v-if="isCoachExpanded(coach.id)" class="border-t border-gray-100 px-4 py-3">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="slot in coach.time_slots"
                :key="slot.id"
                type="button"
                class="px-3 py-1.5 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors"
                @click="selectCoachForBooking(coach, slot)"
              >
                {{ slotLabel(slot) }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

import { http, query } from '../services/http.js'

const today = new Date().toISOString().split('T')[0]
const selectedDate = ref(today)

const coaches = ref([])
const loading = ref(false)
const errorMessage = ref('')

const expandedCoachIds = ref({})

function shiftDate(days) {
  const d = new Date(`${selectedDate.value}T00:00:00`)
  d.setDate(d.getDate() + days)

  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  selectedDate.value = `${y}-${m}-${day}`
}

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  const dateObj = new Date(`${selectedDate.value}T00:00:00`)
  return dateObj.toLocaleDateString(undefined, {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
})

const hhmm = (value) => String(value || '').slice(0, 5)
function slotLabel(slot) {
  return `${hhmm(slot?.start_time)}–${hhmm(slot?.end_time)}`
}

function initials(name) {
  const cleaned = String(name || 'Coach').replace(/^Coach\s+/i, '')
  const parts = cleaned.split(' ').filter(Boolean)
  const letters = parts.map(w => w[0]).join('')
  return letters.toUpperCase().slice(0, 2) || 'C'
}

const avatarPalette = [
  'bg-blue-100 text-blue-700',
  'bg-indigo-100 text-indigo-700',
  'bg-amber-100 text-amber-700',
  'bg-emerald-100 text-emerald-700',
  'bg-rose-100 text-rose-700',
]
function avatarClass(id) {
  const str = String(id ?? '')
  let hash = 0
  for (let i = 0; i < str.length; i++) {
    hash = (hash * 31 + str.charCodeAt(i)) >>> 0
  }
  return avatarPalette[hash % avatarPalette.length]
}

function isCoachExpanded(coachId) {
  return Boolean(expandedCoachIds.value[coachId])
}

function toggleCoach(coachId) {
  expandedCoachIds.value = {
    ...expandedCoachIds.value,
    [coachId]: !expandedCoachIds.value[coachId],
  }
}

let loadToken = 0
async function loadNoticeBoard() {
  const token = ++loadToken
  loading.value = true
  errorMessage.value = ''
  expandedCoachIds.value = {}

  try {
    const res = await http.get(`/notice-board/coaches${query({ date: selectedDate.value })}`)
    if (token !== loadToken) return

    const data = Array.isArray(res?.data) ? res.data : []
    coaches.value = data
  } catch (e) {
    if (token !== loadToken) return
    coaches.value = []
    errorMessage.value = e?.message ?? 'Unable to load coach availability.'
  } finally {
    if (token !== loadToken) return
    loading.value = false
  }
}

function selectCoachForBooking(coach, slot) {
  const coachName = coach?.name || 'Coach'
  alert(`Proceeding to Private Coach Session booking for ${coachName} on ${selectedDate.value} at ${slotLabel(slot)}`)
}

watch(selectedDate, () => {
  loadNoticeBoard()
})

onMounted(() => {
  loadNoticeBoard()
})
</script>
