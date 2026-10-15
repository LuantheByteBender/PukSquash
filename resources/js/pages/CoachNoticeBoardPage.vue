<template>
  <div class="page notice-board">
    <div class="page-head">
      <div>
        <p class="eyebrow">Squash coaching</p>
        <h1>Coach Notice Board</h1>
        <p class="muted">Pick a date, then expand a time slot to see which coaches are available.</p>
      </div>
    </div>

    <div class="card datebar-card">
      <button
        type="button"
        class="btn btn-secondary btn-sm day-btn"
        title="Previous day"
        :disabled="selectedDate <= today"
        @click="shiftDate(-1)"
      >
        ‹
      </button>

      <div class="datebar-mid">
        <input
          v-model="selectedDate"
          type="date"
          :min="today"
        />
        <p class="date-label">{{ formattedSelectedDate }}</p>
      </div>

      <button
        type="button"
        class="btn btn-secondary btn-sm day-btn"
        title="Next day"
        @click="shiftDate(1)"
      >
        ›
      </button>
    </div>

    <section class="coaches-section">
      <div class="section-head">
        <h2 class="section-title">Available coaches</h2>
        <span v-if="loading" class="faint">Refreshing…</span>
      </div>

      <p v-if="errorMessage" class="alert alert-error">{{ errorMessage }}</p>

      <div v-else-if="!loading && coaches.length === 0" class="card empty-card">
        <p class="muted">No coaches have published availability for this date.</p>
      </div>

      <div v-else class="coach-list">
        <div v-for="coach in coaches" :key="coach.id" class="card coach-card">
          <button
            type="button"
            class="coach-toggle"
            @click="toggleCoach(coach.id)"
          >
            <div class="coach-info">
              <div class="coach-avatar" :class="avatarClass(coach.id)">
                {{ initials(coach.name) }}
              </div>
              <div class="coach-meta">
                <p class="coach-name">
                  {{ coach.name || 'Coach' }}
                  <span class="coach-fee">{{ formatCents(coach.private_coaching_fee_cents ?? 0) }}</span>
                </p>
                <p class="coach-slots muted">{{ coach.time_slots.length }} slot{{ coach.time_slots.length !== 1 ? 's' : '' }}</p>
              </div>
            </div>

            <span class="caret" :class="{ 'caret--open': isCoachExpanded(coach.id) }">
              ▾
            </span>
          </button>

          <div v-if="isCoachExpanded(coach.id)" class="slot-panel">
            <button
              v-for="slot in coach.time_slots"
              :key="slot.id"
              type="button"
              class="slot-chip"
              @click="selectCoachForBooking(coach, slot)"
            >
              {{ slotLabel(slot) }}
            </button>
          </div>
        </div>
      </div>
    </section>
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
  const iso = `${y}-${m}-${day}`

  if (iso < today) return

  selectedDate.value = iso
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

function formatCents(value) {
  const amount = Number(value ?? 0) / 100
  return new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
    minimumFractionDigits: 2,
  }).format(amount)
}

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
  'avatar--blue',
  'avatar--purple',
  'avatar--amber',
  'avatar--green',
  'avatar--rose',
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

<style scoped>
.notice-board {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.page-head {
    margin-bottom: 0;
}

.page-head h1 {
    margin: 0.2rem 0 0;
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.02em;
}

.page-head p {
    margin: 0;
}

.eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ps-brand);
}

.datebar-card {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--ps-surface);
    padding: 0.35rem;
    border-radius: var(--ps-radius);
    border: 1px solid var(--ps-border-soft);
    box-shadow: var(--ps-shadow-sm);
}

.datebar-mid {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.datebar-mid input {
    width: auto;
    min-width: 150px;
    border: none;
    background: transparent;
    box-shadow: none;
    font-weight: 600;
}

.datebar-mid input:focus {
    box-shadow: none;
    border: none;
}

.date-label {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 650;
    color: var(--ps-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.coaches-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.section-title {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--ps-text-muted);
}

.empty-card {
    padding: 2.5rem 1rem;
    text-align: center;
}

.coach-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    max-height: 520px;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.coach-card {
    padding: 0;
    overflow: hidden;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.coach-card:hover {
    box-shadow: var(--ps-shadow);
    border-color: rgba(108, 62, 148, 0.15);
}

.coach-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.9rem 1.15rem;
    background: none;
    border: 0;
    cursor: pointer;
    font: inherit;
    text-align: left;
    transition: background 0.15s ease;
}

.coach-toggle:hover {
    background: rgba(108, 62, 148, 0.04);
}

.coach-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.coach-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 800;
    flex-shrink: 0;
}

.avatar--blue { background: #dbeafe; color: #1e40af; }
.avatar--purple { background: var(--ps-brand-tint); color: var(--ps-brand); }
.avatar--amber { background: #fef3c7; color: #92400e; }
.avatar--green { background: #d1fae5; color: #065f46; }
.avatar--rose { background: #ffe4e6; color: #9f1239; }

.coach-meta {
    min-width: 0;
}

.coach-name {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 700;
    color: #1f1630;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.coach-fee {
    margin-left: 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #7e22ce;
}

.coach-slots {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
}

.caret {
    font-size: 0.85rem;
    color: var(--ps-text-faint);
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s;
}

.caret--open {
    transform: rotate(180deg);
    color: var(--ps-brand);
}

.slot-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0.8rem 1.15rem;
    border-top: 1px solid var(--ps-border-soft);
    background: rgba(242, 236, 247, 0.2);
}

.slot-chip {
    padding: 0.4rem 0.75rem;
    font: inherit;
    font-size: 0.78rem;
    font-weight: 650;
    border-radius: var(--ps-radius-sm);
    background: var(--ps-brand-tint);
    color: var(--ps-brand);
    border: 1px solid #ddcfe9;
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.slot-chip:hover {
    background: #e8ddf3;
    border-color: var(--ps-brand-light);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(108, 62, 148, 0.1);
}

.slot-chip:active {
    transform: translateY(0) scale(0.97);
}
</style>
