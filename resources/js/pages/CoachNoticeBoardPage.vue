<template>
  <div class="notice-board">
    <div class="header">
      <div>
        <p class="header-eyebrow">Squash Coaching</p>
        <h1 class="header-title">Coach Notice Board</h1>
        <p class="header-subtitle">Choose a date, then expand a time to see which coaches are available.</p>
      </div>
    </div>

    <div class="date-nav">
      <button class="date-arrow" @click="shiftDate(-1)" title="Previous day">&larr;</button>
      <div class="date-center">
        <input
          v-model="selectedDate"
          type="date"
          class="date-input"
        />
        <p class="date-pretty">{{ formattedSelectedDate }}</p>
      </div>
      <button class="date-arrow" @click="shiftDate(1)" title="Next day">&rarr;</button>
    </div>

    <div class="layout-section">
      <div class="toolbar">
        <p class="toolbar-hint">Tap a time slot to expand.</p>
        <LoadingSpinner v-if="loading" label="Refreshing…" />
      </div>

      <div v-if="loadError" class="error-card">
        {{ loadError }}
      </div>

      <div v-if="!loading && visibleTimeSlots.length === 0" class="empty-state">
        <span class="empty-icon">&#128197;</span>
        <p>No coaches have published availability for this date.</p>
      </div>

      <div v-else class="timeline">
        <div v-for="(slot, idx) in visibleTimeSlots" :key="slot.id" class="tl-row">
          <div class="tl-gutter">
            <div :class="['tl-dot', { 'tl-dot-active': expandedSlotIds.has(slot.id) }]"></div>
            <div v-if="idx < visibleTimeSlots.length - 1" class="tl-line"></div>
          </div>

          <div :class="['tl-card', { 'tl-card-open': expandedSlotIds.has(slot.id) }]">
            <button class="tl-header" @click="toggleSlot(slot.id)">
              <div class="tl-header-left">
                <span class="tl-time">{{ slotLabel(slot) }}</span>
                <span class="tl-count">
                  {{ slotCount(slot.id) }}
                </span>
              </div>
              <span :class="['tl-chevron', { 'tl-chevron-open': expandedSlotIds.has(slot.id) }]">&#9662;</span>
            </button>

            <div v-if="expandedSlotIds.has(slot.id)" class="tl-body">
              <div
                v-for="coach in coachesForSlotId(slot.id)"
                :key="coach.id"
                class="coach-row"
              >
                <div class="coach-row-left">
                  <div :class="['avatar', avatarColor(coach.id)]">{{ initials(coach.name) }}</div>
                  <div>
                    <p class="coach-name">{{ coach.name || 'Coach' }}</p>
                  </div>
                </div>
                <button class="btn-book-sm" @click="selectCoachForBooking(coach, slot)">Book</button>
              </div>
              <p v-if="coachesForSlotId(slot.id).length === 0" class="empty-inline">No coaches at this time.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

import LoadingSpinner from '../components/LoadingSpinner.vue'
import { bookingService } from '../features/booking/bookingService.js'
import { http } from '../services/http.js'

// Default to today's date (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0]
const selectedDate = ref(today)

const timeSlots = ref([])
const coachesBySlotId = ref({})

const loadingTimeSlots = ref(false)
const loadingCoaches = ref(false)
const loadError = ref('')

const loading = computed(() => loadingTimeSlots.value || loadingCoaches.value)

const visibleTimeSlots = computed(() => {
  if (loadingCoaches.value) {
    return timeSlots.value
  }

  return timeSlots.value.filter((slot) => {
    const coaches = coachesBySlotId.value[slot?.id]?.coaches ?? []
    return coaches.length > 0
  })
})

function shiftDate(days) {
  const d = new Date(`${selectedDate.value}T00:00:00`)
  d.setDate(d.getDate() + days)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  selectedDate.value = `${y}-${m}-${day}`
}

// Formatted Date string display
const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  const dateObj = new Date(`${selectedDate.value}T00:00:00`)
  return dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
})

// Avatar color palette
const avatarColors = [
  'avatar-blue', 'avatar-violet', 'avatar-amber', 'avatar-emerald', 'avatar-rose',
]
function avatarColor(id) {
  const str = String(id ?? '')
  let hash = 0
  for (let i = 0; i < str.length; i++) {
    hash = (hash * 31 + str.charCodeAt(i)) >>> 0
  }
  return avatarColors[hash % avatarColors.length]
}

const hhmm = (value) => String(value || '').slice(0, 5)

function slotLabel(slot) {
  return `${hhmm(slot?.start_time)}–${hhmm(slot?.end_time)}`
}

// Initials helper
function initials(name) {
  const cleaned = String(name || 'Coach').replace(/^Coach\s+/i, '')
  const parts = cleaned.split(' ').filter(Boolean)
  const letters = parts.map(w => w[0]).join('')
  return letters.toUpperCase().slice(0, 2) || 'C'
}

const expandedSlotIds = ref(new Set())

function toggleSlot(slotId) {
  if (expandedSlotIds.value.has(slotId)) {
    expandedSlotIds.value.delete(slotId)
  } else {
    expandedSlotIds.value.add(slotId)
  }
}

function coachesForSlotId(slotId) {
  return coachesBySlotId.value[slotId]?.coaches ?? []
}

function slotCount(slotId) {
  if (loading.value && !(slotId in coachesBySlotId.value)) {
    return '…'
  }

  const count = coachesForSlotId(slotId).length
  return `${count} coach${count !== 1 ? 'es' : ''}`
}

async function loadTimeSlots() {
  loadingTimeSlots.value = true
  loadError.value = ''

  try {
    const slots = await http.get('/time-slots')
    timeSlots.value = (Array.isArray(slots) ? slots : []).slice().sort(
      (a, b) => (a?.display_order ?? 0) - (b?.display_order ?? 0)
    )
  } catch (e) {
    timeSlots.value = []
    loadError.value = e?.message ?? String(e)
  } finally {
    loadingTimeSlots.value = false
  }
}

let loadToken = 0
async function loadCoachesForDate() {
  const token = ++loadToken
  loadingCoaches.value = true
  loadError.value = ''
  expandedSlotIds.value = new Set()

  try {
    const date = selectedDate.value
    const slots = timeSlots.value

    const results = await Promise.all(
      slots.map(async (slot) => {
        try {
          const coaches = await bookingService.availableCoaches(date, slot.id)
          return [slot.id, Array.isArray(coaches) ? coaches : []]
        } catch (e) {
          return [slot.id, []]
        }
      })
    )

    if (token !== loadToken) return

    const map = {}
    for (const [slotId, coaches] of results) {
      map[slotId] = { coaches }
    }
    coachesBySlotId.value = map
  } catch (e) {
    if (token !== loadToken) return
    coachesBySlotId.value = {}
    loadError.value = e?.message ?? String(e)
  } finally {
    if (token !== loadToken) return
    loadingCoaches.value = false
  }
}

watch(selectedDate, async () => {
  if (!timeSlots.value.length) return
  await loadCoachesForDate()
})

onMounted(async () => {
  await loadTimeSlots()
  await loadCoachesForDate()
})

function selectCoachForBooking(coach, slot) {
  const coachName = coach?.name || 'Coach'
  alert(`Proceeding to Private Coach Session booking for ${coachName} on ${selectedDate.value} at ${slotLabel(slot)}`)
}
</script>

<style scoped>
/* ─── Page shell ─── */
.notice-board {
  max-width: 56rem;
  margin: 0 auto;
  padding: 2rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ─── Header ─── */
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #e5e7eb;
}
.header-eyebrow {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6366f1;
}
.header-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
  margin-top: 0.15rem;
}
.header-subtitle {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

/* ─── Tab bar ─── */
/* ─── Toolbar ─── */
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}
.toolbar-hint {
  font-size: 0.82rem;
  color: #6b7280;
}

.error-card {
  background: #fff;
  border: 1px solid #fecaca;
  color: #991b1b;
  border-radius: 14px;
  padding: 0.85rem 1rem;
  font-size: 0.85rem;
}

/* ─── Date nav ─── */
.date-nav {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 0.6rem 0.85rem;
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
}
.date-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  font-size: 0.9rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
  flex-shrink: 0;
}
.date-arrow:hover {
  background: #eef2ff;
  border-color: #c7d2fe;
  color: #4338ca;
}
.date-center {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}
.date-input {
  padding: 0.35rem 0.5rem;
  font-size: 0.8rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.15s;
  color: #374151;
}
.date-input:focus {
  border-color: #818cf8;
  box-shadow: 0 0 0 2px rgb(99 102 241 / 0.15);
}
.date-pretty {
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
}

/* ─── Layout section (shared wrapper) ─── */
.layout-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ─── Section helpers ─── */
 

/* ─── Avatar ─── */
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 700;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}
.avatar:hover { transform: scale(1.08); }
.avatar-lg { width: 48px; height: 48px; font-size: 0.85rem; }
.avatar-blue { background: #dbeafe; color: #2563eb; }
.avatar-violet { background: #ede9fe; color: #7c3aed; }
.avatar-amber { background: #fef3c7; color: #b45309; }
.avatar-emerald { background: #d1fae5; color: #059669; }
.avatar-rose { background: #ffe4e6; color: #e11d48; }

/* ─── Active pill ─── */
/* ─── Coach name ─── */
.coach-name { font-size: 0.85rem; font-weight: 700; color: #111827; line-height: 1.2; }

/* ─── Buttons ─── */
.btn-book-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #fff;
  background: #4f46e5;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}
.btn-book-sm:hover {
  background: #4338ca;
  box-shadow: 0 2px 8px rgb(79 70 229 / 0.25);
}

/* ─── Empty states ─── */
.empty-state {
  background: #fff;
  border: 1px dashed #d1d5db;
  border-radius: 14px;
  padding: 3rem 1.5rem;
  text-align: center;
  color: #9ca3af;
  font-size: 0.85rem;
}
.empty-icon {
  display: block;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}
.empty-inline {
  font-size: 0.78rem;
  color: #9ca3af;
  text-align: center;
  padding: 0.75rem 0;
}

/* ─── Coach row (shared by A & C) ─── */
.coach-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  background: #f9fafb;
  border: 1px solid #f3f4f6;
  border-radius: 10px;
  padding: 0.6rem 0.75rem;
  transition: background 0.15s, border-color 0.15s;
}
.coach-row:hover {
  background: #eef2ff;
  border-color: #e0e7ff;
}
.coach-row-left {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  min-width: 0;
}

/* ═══════════════════════════════════════════════ */
/* LAYOUT A — Timeline                            */
/* ═══════════════════════════════════════════════ */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.tl-row {
  display: flex;
  gap: 0.75rem;
  min-height: 0;
}
.tl-gutter {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 18px;
  flex-shrink: 0;
  padding-top: 1rem;
}
.tl-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2.5px solid #c7d2fe;
  background: #fff;
  flex-shrink: 0;
  transition: all 0.2s ease;
  z-index: 1;
}
.tl-dot-active {
  border-color: #4f46e5;
  background: #4f46e5;
  box-shadow: 0 0 0 4px rgb(79 70 229 / 0.15);
}
.tl-line {
  flex: 1;
  width: 2px;
  background: #e5e7eb;
  margin: 4px 0;
}
.tl-card {
  flex: 1;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 0.6rem;
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.tl-card:hover {
  border-color: #c7d2fe;
}
.tl-card-open {
  border-color: #a5b4fc;
  box-shadow: 0 2px 12px rgb(99 102 241 / 0.08);
}
.tl-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background 0.15s;
}
.tl-header:hover {
  background: #f9fafb;
}
.tl-header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.tl-time {
  font-size: 0.85rem;
  font-weight: 700;
  color: #111827;
}
.tl-count {
  font-size: 0.68rem;
  font-weight: 500;
  color: #9ca3af;
  background: #f3f4f6;
  padding: 0.12rem 0.45rem;
  border-radius: 9999px;
}
.tl-chevron {
  font-size: 0.7rem;
  color: #9ca3af;
  transition: transform 0.25s ease;
}
.tl-chevron-open {
  transform: rotate(180deg);
  color: #4f46e5;
}
.tl-body {
  padding: 0 1rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  border-top: 1px solid #f3f4f6;
  padding-top: 0.6rem;
}

/* ─── Responsive ─── */
@media (max-width: 640px) {
  .notice-board { padding: 1.25rem 0.75rem; }
  .header-title { font-size: 1.25rem; }
  .date-pretty { display: none; }
}
</style>