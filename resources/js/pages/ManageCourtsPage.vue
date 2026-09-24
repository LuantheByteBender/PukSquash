@@
<template>
  <main class="page manage-courts-page">

    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1 class="page-title">Manage Courts</h1>
      </div>

      <div class="row">
        <button type="button" class="btn btn-secondary" :disabled="isWorking" @click="openAddModal">
          Add court
        </button>
        <button type="button" class="btn btn-secondary" @click="router.push('/admin/dashboard')">
          Back to Dashboard
        </button>
      </div>
    </header>

    <p v-if="loadError" class="alert alert-error">{{ loadError }}</p>
    <p v-else-if="actionError" class="alert alert-error">{{ actionError }}</p>

    <section class="card">
      <div class="row table-controls">
        <label class="field grow">
          <span class="label">Court name</span>
          <input v-model="searchQuery" type="text" placeholder="Search courts..." />
        </label>
      </div>

      <p v-if="isLoading" class="muted">Loading courts...</p>

      <div class="table-wrap" v-else>
        <table class="users-table">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Court name</th>
              <th scope="col">Description</th>
              <th scope="col">Status</th>
              <th scope="col" class="actions">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="court in filteredCourts" :key="court.id">
              <td class="muted">#{{ court.id }}</td>
              <td>
                <strong>{{ court.name }}</strong>
              </td>
              <td class="muted">{{ court.description || '-' }}</td>
              <td>
                <span class="pill" :class="statusPillVariant(court.status)">
                  {{ court.status === 'ACTIVE' ? 'ACTIVE' : 'IN MAINTENANCE' }}
                </span>
              </td>
              <td class="actions">
                <button
                  type="button"
                  class="btn btn-secondary btn-sm"
                  :disabled="isWorking"
                  @click="openEditModal(court)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="btn btn-secondary btn-sm"
                  :disabled="isWorking"
                  @click="toggleStatus(court)"
                >
                  Toggle status
                </button>
                <button
                  type="button"
                  class="btn btn-danger btn-sm"
                  :disabled="isWorking"
                  @click="deleteCourt(court.id)"
                >
                  Delete
                </button>
              </td>
            </tr>

            <tr v-if="filteredCourts.length === 0 && !isLoading">
              <td colspan="5" class="muted empty">No courts found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div v-if="showModal" class="backdrop" @click.self="closeModal">
      <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="court-modal-title">
        <h2 id="court-modal-title">{{ isEditing ? 'Edit court' : 'Add new court' }}</h2>

        <form class="form" @submit.prevent="saveCourt">
          <label class="field">
            <span class="label">Court name</span>
            <input v-model="courtForm.name" type="text" required :disabled="isWorking" />
          </label>

          <label class="field">
            <span class="label">Description</span>
            <input v-model="courtForm.description" type="text" :disabled="isWorking" />
          </label>

          <label v-if="!isEditing" class="field">
            <span class="label">Status</span>
            <select v-model="courtForm.status" :disabled="isWorking">
              <option value="ACTIVE">Active</option>
              <option value="INACTIVE">In maintenance</option>
            </select>
          </label>

          <div class="actions-row">
            <button type="button" class="btn btn-secondary" :disabled="isWorking" @click="closeModal">
              Cancel
            </button>
            <button type="submit" class="btn" :disabled="isWorking">
              {{ isEditing ? 'Update court' : 'Save court' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-text="confirmState.confirmText"
      :confirm-variant="confirmState.confirmVariant"
      @confirm="resolveConfirm(true)"
      @cancel="resolveConfirm(false)"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import ConfirmDialog from '../components/ConfirmDialog.vue'
import { useToast } from '../composables/useToast.js'
import { http } from '../services/http.js'

// Modal & State Controls
const showModal = ref(false)
const isEditing = ref(false)
const searchQuery = ref('')
const editingCourtId = ref(null)

const router = useRouter()
const toast = useToast()

const isLoading = ref(false)
const loadError = ref('')
const actionError = ref('')
const isWorking = ref(false)

const confirmState = ref({
  open: false,
  title: 'Confirm',
  message: '',
  confirmText: 'Confirm',
  confirmVariant: 'primary',
  resolve: null,
})

function requestConfirm({ title, message, confirmText, confirmVariant }) {
  confirmState.value.open = true
  confirmState.value.title = title ?? 'Confirm'
  confirmState.value.message = message ?? ''
  confirmState.value.confirmText = confirmText ?? 'Confirm'
  confirmState.value.confirmVariant = confirmVariant ?? 'primary'

  return new Promise((resolve) => {
    confirmState.value.resolve = resolve
  })
}

function resolveConfirm(value) {
  const resolve = confirmState.value.resolve
  confirmState.value.open = false
  confirmState.value.resolve = null
  if (typeof resolve === 'function') {
    resolve(value)
  }
}

const courts = ref([])

// Form Data Object
const courtForm = ref({
  name: '',
  description: '',
  status: 'ACTIVE',
})

// Search Filter Computation
const filteredCourts = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return courts.value.filter(court => 
    !query ||
    court.name.toLowerCase().includes(query) ||
    (court.description || '').toLowerCase().includes(query)
  )
})

function statusPillVariant(status) {
  if (status === 'ACTIVE') return ''
  return 'pill-grey'
}

function normalizeCourt(court) {
  return {
    id: court.id,
    name: court.name ?? '',
    description: court.description ?? null,
    status: court.status ?? 'INACTIVE',
  }
}

async function loadCourts() {
  isLoading.value = true
  loadError.value = ''
  actionError.value = ''

  try {
    const response = await http.get('/admin/courts')
    const payload = Array.isArray(response)
      ? response
      : Array.isArray(response?.courts)
        ? response.courts
        : []

    courts.value = payload.map(normalizeCourt)
  } catch (error) {
    console.error('Failed to load courts:', error)
    loadError.value = 'Unable to load courts right now. Please try again.'
    courts.value = []
  } finally {
    isLoading.value = false
  }
}

// Modal Actions
function openAddModal() {
  isEditing.value = false
  editingCourtId.value = null
  courtForm.value = { name: '', description: '', status: 'ACTIVE' }
  showModal.value = true
}

function openEditModal(court) {
  isEditing.value = true
  editingCourtId.value = court.id
  courtForm.value = { name: court.name, description: court.description ?? '', status: court.status ?? 'INACTIVE' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function saveCourt() {
  actionError.value = ''

  if (isWorking.value) return

  isWorking.value = true

  try {
    if (isEditing.value) {
      await http.put(`/admin/courts/${editingCourtId.value}`, {
        name: courtForm.value.name,
        description: courtForm.value.description,
      })
      toast.success('Court updated.')
    } else {
      await http.post('/admin/courts', {
        name: courtForm.value.name,
        description: courtForm.value.description,
        status: courtForm.value.status,
      })
      toast.success('Court created.')
    }

    closeModal()
    await loadCourts()
  } catch (error) {
    console.error('Failed to save court:', error)
    actionError.value = 'Unable to save changes. Please try again.'
    toast.error(actionError.value)
  } finally {
    isWorking.value = false
  }
}

async function toggleStatus(court) {
  actionError.value = ''
  if (isWorking.value) return

  const nextStatus = court.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'

  const ok = await requestConfirm({
    title: 'Confirm status change',
    message: `Set "${court.name}" to ${nextStatus === 'ACTIVE' ? 'Active' : 'In maintenance'}?`,
    confirmText: 'Confirm',
  })
  if (!ok) return

  isWorking.value = true

  try {
    const updated = await http.put(`/admin/courts/${court.id}`, { status: nextStatus })
    const normalized = normalizeCourt(updated ?? { ...court, status: nextStatus })
    courts.value = courts.value.map((row) => (row.id === court.id ? normalized : row))
    toast.success('Court updated.')
  } catch (error) {
    console.error('Failed to toggle court status:', error)
    actionError.value = 'Unable to update court status. Please try again.'
    toast.error(actionError.value)
  } finally {
    isWorking.value = false
  }
}

async function deleteCourt(id) {
  actionError.value = ''
  if (isWorking.value) return

  const court = courts.value.find((c) => c.id === id)
  const who = court?.name || 'this court'

  const ok = await requestConfirm({
    title: 'Delete court?',
    message: `Permanently delete ${who}? This cannot be undone.`,
    confirmText: 'Delete',
    confirmVariant: 'danger',
  })
  if (!ok) return

  isWorking.value = true

  try {
    await http.delete(`/admin/courts/${id}`)
    courts.value = courts.value.filter((c) => c.id !== id)
    toast.success('Court deleted.')
  } catch (error) {
    console.error('Failed to delete court:', error)
    actionError.value = 'Unable to delete court. Please try again.'
    toast.error(actionError.value)
  } finally {
    isWorking.value = false
  }
}

onMounted(() => {
  loadCourts()
})
</script>

<style scoped>
 .manage-courts-page {
     display: flex;
     flex-direction: column;
     gap: 1.5rem;
     padding-top: 1.5rem;
 }
 
 .page-header {
     display: flex;
     align-items: center;
     justify-content: space-between;
     gap: 1rem;
     flex-wrap: wrap;
 }
 
 .eyebrow {
     margin: 0;
     text-transform: uppercase;
     letter-spacing: 0.08em;
     font-size: 0.75rem;
     font-weight: 700;
     color: var(--ps-brand);
 }
 
 .page-title {
     margin: 0.25rem 0 0;
     font-size: clamp(2rem, 4vw, 2.5rem);
     line-height: 1.1;
 }
 
 .table-controls {
     align-items: flex-end;
     margin-bottom: 1rem;
 }
 
 .field {
     display: flex;
     flex-direction: column;
     gap: 0.35rem;
     min-width: 220px;
 }
 
 .field.grow {
     flex: 1;
     min-width: 260px;
 }
 
 .label {
     font-size: 0.75rem;
     font-weight: 700;
     color: var(--ps-text-muted);
     letter-spacing: 0.04em;
     text-transform: uppercase;
 }
 
 .table-wrap {
     overflow-x: auto;
 }
 
 .users-table {
     width: 100%;
     border-collapse: collapse;
     min-width: 760px;
 }
 
 .users-table th {
     text-align: left;
     font-size: 0.75rem;
     font-weight: 700;
     letter-spacing: 0.04em;
     text-transform: uppercase;
     color: var(--ps-text-muted);
     padding: 0.75rem 0.7rem;
     border-bottom: 1px solid var(--ps-border-soft);
 }
 
 .users-table td {
     padding: 0.75rem 0.7rem;
     border-bottom: 1px solid var(--ps-border-soft);
     vertical-align: middle;
 }
 
 .users-table tbody tr:hover {
     background: rgba(108, 62, 148, 0.02);
 }
 
 .actions {
     width: 260px;
 }
 
 .users-table td.actions {
     display: flex;
     gap: 0.5rem;
     flex-wrap: wrap;
 }
 
 .empty {
     padding: 1rem 0.7rem;
 }
 
 .backdrop {
     position: fixed;
     inset: 0;
     z-index: 80;
     padding: 1rem;
     background: rgba(24, 14, 33, 0.55);
     display: grid;
     place-items: center;
 }
 
 .dialog {
     max-width: 520px;
     width: 100%;
     background: var(--ps-surface);
     border-radius: var(--ps-radius);
     padding: 1.1rem;
     border: 1px solid var(--ps-border-soft);
     box-shadow: 0 10px 30px rgba(24, 14, 33, 0.25);
 }
 
 .dialog h2 {
     margin: 0 0 0.75rem;
     font-size: 1.15rem;
 }
 
 .form {
     display: flex;
     flex-direction: column;
     gap: 0.85rem;
 }
 
 .actions-row {
     display: flex;
     gap: 0.6rem;
     justify-content: flex-end;
     margin-top: 0.25rem;
     flex-wrap: wrap;
 }
 
 @media (max-width: 760px) {
     .manage-courts-page {
         gap: 1rem;
         padding-top: 1rem;
     }
 
     .page-header {
         flex-direction: column;
         align-items: stretch;
     }
 
     .page-title {
         font-size: clamp(1.8rem, 8vw, 2.2rem);
     }
 
     .table-controls {
         flex-direction: column;
         align-items: stretch;
         margin-bottom: 0.75rem;
     }
 
     .field {
         min-width: 0;
     }
 
     .users-table {
         min-width: 680px;
     }
 }
 </style>
