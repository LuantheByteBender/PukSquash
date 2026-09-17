<template>
  <div class="p-8 max-w-6xl mx-auto">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manage Courts</h1>
        <p class="text-sm text-gray-500 mt-1">View, add, edit, and toggle facility court availability.</p>
      </div>
      <button 
        @click="openAddModal" 
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2.5 rounded-lg shadow-sm transition-colors"
      >
        <span class="text-lg leading-none">+</span> Add New Court
      </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="mb-5">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search court name..." 
        class="w-full sm:w-72 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
      />
    </div>

    <!-- Courts Data Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3.5">ID</th>
            <th class="px-6 py-3.5">Court Name</th>
            <th class="px-6 py-3.5">Type</th>
            <th class="px-6 py-3.5">Status</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
          <tr v-for="court in filteredCourts" :key="court.id" class="hover:bg-gray-50/80 transition-colors">
            <td class="px-6 py-4 font-mono text-gray-400">#{{ court.id }}</td>
            <td class="px-6 py-4 font-semibold text-gray-900">{{ court.name }}</td>
            <td class="px-6 py-4 text-gray-600">{{ court.type }}</td>
            <td class="px-6 py-4">
              <span 
                :class="court.is_active ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              >
                <span :class="court.is_active ? 'bg-green-500' : 'bg-red-500'" class="w-1.5 h-1.5 rounded-full"></span>
                {{ court.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end items-center gap-2">
                <!-- 1. EDIT BUTTON -->
                <button 
                  @click="openEditModal(court)" 
                  class="text-xs font-medium text-blue-600 border border-blue-200 px-3 py-1.5 rounded-md hover:bg-blue-50 transition-colors"
                >
                  Edit
                </button>
                <!-- 2. TOGGLE STATUS BUTTON -->
                <button 
                  @click="toggleStatus(court)" 
                  class="text-xs font-medium text-gray-600 border border-gray-300 px-3 py-1.5 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                >
                  Toggle Status
                </button>
                <!-- DELETE BUTTON -->
                <button 
                  @click="deleteCourt(court.id)" 
                  class="text-xs font-medium text-red-600 px-3 py-1.5 rounded-md hover:bg-red-50 transition-colors"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredCourts.length === 0">
            <td colspan="5" class="px-6 py-16 text-center">
              <p class="text-gray-500 text-sm">No courts found.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-7">
        <h2 class="text-lg font-bold text-gray-900 mb-5">
          {{ isEditing ? 'Edit Court' : 'Add New Court' }}
        </h2>
        <form @submit.prevent="saveCourt" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Court Name</label>
            <input 
              v-model="courtForm.name" 
              type="text" 
              required 
              placeholder="e.g. Court 4 (Glass)" 
              class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Court Type</label>
            <select 
              v-model="courtForm.type" 
              class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow bg-white"
            >
              <option value="Glass Wall">Glass Wall</option>
              <option value="Standard">Standard</option>
            </select>
          </div>
          <div class="flex justify-end gap-3 pt-3">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-sm transition-colors"
            >
              {{ isEditing ? 'Update Court' : 'Save Court' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Modal & State Controls
const showModal = ref(false)
const isEditing = ref(false)
const searchQuery = ref('')
const editingCourtId = ref(null)

// Reactive Sample Data
const courts = ref([
  { id: 1, name: 'Court 1 (Glass)', type: 'Glass Wall', is_active: true },
  { id: 2, name: 'Court 2 (Standard)', type: 'Standard', is_active: true },
  { id: 3, name: 'Court 3 (Standard)', type: 'Standard', is_active: false },
])

// Form Data Object
const courtForm = ref({
  name: '',
  type: 'Standard'
})

// Search Filter Computation
const filteredCourts = computed(() => {
  return courts.value.filter(court => 
    court.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Modal Actions
function openAddModal() {
  isEditing.value = false
  editingCourtId.value = null
  courtForm.value = { name: '', type: 'Standard' }
  showModal.value = true
}

function openEditModal(court) {
  isEditing.value = true
  editingCourtId.value = court.id
  courtForm.value = { name: court.name, type: court.type }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

// 1. CREATE & EDIT COURT HANDLER
function saveCourt() {
  if (isEditing.value) {
    // Edit existing court
    const target = courts.value.find(c => c.id === editingCourtId.value)
    if (target) {
      target.name = courtForm.value.name
      target.type = courtForm.value.type
    }
  } else {
    // Create new court
    courts.value.push({
      id: courts.value.length ? Math.max(...courts.value.map(c => c.id)) + 1 : 1,
      name: courtForm.value.name,
      type: courtForm.value.type,
      is_active: true
    })
  }
  closeModal()
}

// 2. TOGGLE ACTIVE / INACTIVE HANDLER
function toggleStatus(court) {
  court.is_active = !court.is_active
}

// DELETE HANDLER
function deleteCourt(id) {
  courts.value = courts.value.filter(c => c.id !== id)
}
</script>