<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import { http } from '../services/http.js';

const router = useRouter();

// Stores user data, loading state , filters and editing state
const users = ref([]);
const isLoading = ref(false);
const loadError = ref('');
const search = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const editingUserId = ref(null);
const draftRole = ref('USER');
const draftStatus = ref('ACTIVE');

// Available options for user roles and statuses
const roleOptions = ['USER', 'COACH', 'ADMIN'];
const statusOptions = ['ACTIVE', 'INACTIVE'];

// Standardizes user data to ensure consistent structure and default values
function normalizeUser(user) {
    return {
        id: user.id ?? user.firebaseUid ?? user.email,
        fullName:
            user.fullName ||
            user.full_name ||
            user.name ||
            (user.email ? user.email.split('@')[0] : 'Unknown user'),
        email: user.email || '',
        role: user.role || 'USER',
        status: user.status || 'ACTIVE',
    };
}


// Loads users from the backend API and normalizes the data for consistent structure
async function loadUsers() {
    isLoading.value = true;
    loadError.value = '';

    try {
        const response = await http.get('/users');
        const payload = Array.isArray(response)
            ? response
            : Array.isArray(response?.users)
                ? response.users
                : [];

        users.value = payload.map(normalizeUser);
    } catch (error) {
        console.error('Failed to load users:', error);
        loadError.value = 'Unable to load users right now. Please try again.';
        users.value = [];
    } finally {
        isLoading.value = false;
    }
}


// Computes the filtered list of users based on search query, role filter, and status filter
const filteredUsers = computed(() => {
    const query = search.value.trim().toLowerCase();

    return users.value.filter((user) => {
        const matchesQuery =
            !query ||
            user.fullName.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.role.toLowerCase().includes(query) ||
            user.status.toLowerCase().includes(query);

        const matchesRole = !roleFilter.value || user.role === roleFilter.value;
        const matchesStatus = !statusFilter.value || user.status === statusFilter.value;

        return matchesQuery && matchesRole && matchesStatus;
    });
});


// Functions to handle editing, saving, and removing users
function startEdit(user) {
    editingUserId.value = user.id;
    draftRole.value = user.role;
    draftStatus.value = user.status;
}

function cancelEdit() {
    editingUserId.value = null;
    draftRole.value = 'USER';
    draftStatus.value = 'ACTIVE';
}

function saveUser(user) {
    user.role = draftRole.value;
    user.status = draftStatus.value;
    editingUserId.value = null;
    draftRole.value = 'USER';
    draftStatus.value = 'ACTIVE';
}

function removeUser(id) {
    users.value = users.value.filter((user) => user.id !== id);

    if (editingUserId.value === id) {
        cancelEdit();
    }
}

onMounted(() => {
    loadUsers();
});
</script>

<template>
    <main class="page manage-users-page">

        <!-- Page Header With Title and Back Button -->
        <header class="page-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h1 class="page-title">Manage Users</h1>
            </div>

            <button type="button" class="btn btn-secondary back-button" @click="router.push('/admin/dashboard')">
                Back to Dashboard
            </button>
        </header>

        <p v-if="loadError" class="page-message error">{{ loadError }}</p>
        <p v-else-if="isLoading" class="page-message">Loading users...</p>

        <!--Section Filters For Users: Search, Role, Status -->
        <section class="table-filters">
            <div class="filters">
                <label class="filter-field filter-search">
                    <span class="filter-label">User name</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search users..."
                    />
                </label>

                <label class="filter-field">
                    <span class="filter-label">Role</span>
                    <select v-model="roleFilter">
                        <option value="">All roles</option>
                        <option v-for="role in roleOptions" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </label>

                <label class="filter-field">
                    <span class="filter-label">Status</span>
                    <select v-model="statusFilter">
                        <option value="">All statuses</option>
                        <option v-for="status in statusOptions" :key="status" :value="status">
                            {{ status }}
                        </option>
                    </select>
                </label>
            </div>
        </section>

        <!-- Section Contains User Table. -->
        <section class="table-section">
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="actions-heading">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td>
                                <div class="user-cell">
                                    <span class="user-name">{{ user.fullName }}</span>
                                </div>
                            </td>

                            <td>{{ user.email }}</td>

                            <td>
                                <template v-if="editingUserId === user.id">
                                    <select v-model="draftRole" class="inline-select">
                                        <option v-for="role in roleOptions" :key="role" :value="role">
                                            {{ role }}
                                        </option>
                                    </select>
                                </template>

                                <template v-else>
                                    <span class="badge role-badge" :class="`role-${user.role.toLowerCase()}`">
                                        {{ user.role }}
                                    </span>
                                </template>
                            </td>

                            <td>
                                <template v-if="editingUserId === user.id">
                                    <select v-model="draftStatus" class="inline-select">
                                        <option v-for="status in statusOptions" :key="status" :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                </template>

                                <template v-else>
                                    <span class="badge status-badge" :class="`status-${user.status.toLowerCase()}`">
                                        {{ user.status }}
                                    </span>
                                </template>
                            </td>

                            <td class="actions-cell">
                                <div class="actions">
                                    <template v-if="editingUserId === user.id">
                                        <button type="button" class="btn btn-primary" @click="saveUser(user)">
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-secondary" @click="cancelEdit">
                                            Cancel
                                        </button>
                                    </template>

                                    <template v-else>
                                        <button type="button" class="btn btn-secondary" @click="startEdit(user)">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-danger" @click="removeUser(user.id)">
                                            Remove
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</template>

<style scoped>
.manage-users-page {
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

/* =========================
   Back Button
   ========================= */

.back-button {
    margin-left: auto;
    white-space: nowrap;
    flex-shrink: 0;

    background-color: #6c3d91;
    color: #ffffff;
    border: 1px solid #6c3d91;
}

.back-button:hover {
    background-color: #552f73;
    color: #ffffff;
    border-color: #552f73;
}

/* =========================
   Page Heading
   ========================= */

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

/* =========================
   Filters
   ========================= */

.table-filters {
    background: #ffffff;
    border: 1px solid var(--ps-border-soft);
    border-radius: 20px;
    box-shadow: 0 1px 2px rgba(24, 18, 33, 0.04);
    padding: 1rem;
}

.filters {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) repeat(2, minmax(180px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-field {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ps-text-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.filter-field input,
.filter-field select,
.inline-select {
    width: 100%;
    min-height: 42px;
    padding: 0.65rem 0.8rem;
    border-radius: 12px;
    border: 1px solid var(--ps-border);
    background: var(--ps-surface);
    color: var(--ps-text);
    font: inherit;
    box-sizing: border-box;
}

.filter-field input:focus-visible,
.filter-field select:focus-visible,
.inline-select:focus-visible {
    outline: 3px solid rgba(108, 62, 148, 0.2);
    outline-offset: 1px;
}

/* =========================
   Table
   ========================= */

.table-section {
    background: #ffffff;
    border: 1px solid var(--ps-border-soft);
    border-radius: 20px;
    box-shadow: 0 1px 2px rgba(24, 18, 33, 0.04);
    overflow: hidden;
}

.table-shell {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

thead th {
    background: #f7f4fa;
    color: var(--ps-text-muted);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-align: left;
    padding: 0.9rem 0.85rem;
    border-bottom: 1px solid var(--ps-border-soft);
}

tbody td {
    padding: 0.9rem 0.85rem;
    border-bottom: 1px solid var(--ps-border-soft);
    vertical-align: middle;
    color: var(--ps-text);
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody tr:hover {
    background: rgba(108, 62, 148, 0.02);
}

/* =========================
   User
   ========================= */

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-name {
    font-weight: 700;
    color: var(--ps-text);
}

/* =========================
   Badges
   ========================= */

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.role-user {
    background: #f3edf9;
    border-color: rgba(108, 62, 148, 0.2);
    color: var(--ps-brand-dark);
}

.role-coach {
    background: #fff5e5;
    border-color: rgba(185, 120, 18, 0.25);
    color: #8a5209;
}

.role-admin {
    background: rgba(108, 62, 148, 0.12);
    border-color: rgba(108, 62, 148, 0.25);
    color: var(--ps-brand);
}

.status-active {
    background: var(--ps-success-bg);
    border-color: rgba(29, 107, 52, 0.25);
    color: var(--ps-success);
}

.status-inactive {
    background: #f2f2f4;
    border-color: var(--ps-border);
    color: var(--ps-text-muted);
}

/* =========================
   Actions
   ========================= */

.actions-cell {
    width: 235px;
}

.actions-heading {
    width: 235px;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* =========================
   Buttons
   ========================= */

.btn {
    appearance: none;
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 0.55rem 0.9rem;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease,
                transform 0.15s ease,
                color 0.15s ease,
                border-color 0.15s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Primary Button */

.btn-primary {
    background: var(--ps-brand);
    color: #ffffff;
}

.btn-primary:hover {
    background: var(--ps-brand-dark);
    color: #ffffff;
}

/* Secondary Button */

.btn-secondary {
    background: #ffffff;
    color: var(--ps-text);
    border-color: var(--ps-border);
}

.btn-secondary:hover {
    background: #f3f0f7;
    color: var(--ps-text);
}

/* Back Button Override
   Must come AFTER .btn-secondary
*/

.back-button {
    background-color: #6c3d91;
    color: #ffffff;
    border-color: #6c3d91;
}

.back-button:hover {
    background-color: #552f73;
    color: #ffffff;
    border-color: #552f73;
}

/* Danger Button */

.btn-danger {
    background: var(--ps-danger-bg);
    border-color: rgba(179, 38, 30, 0.2);
    color: var(--ps-danger);
}

.btn-danger:hover {
    background: #f7d7d5;
    color: var(--ps-danger);
}

/* =========================
   Responsive Design
   ========================= */

@media (max-width: 760px) {

    .manage-users-page {
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

    .back-button {
        margin-left: 0;
        width: 100%;
        justify-content: center;
        font-size: 0.9rem;
        padding: 0.7rem 0.9rem;
    }

    .filters {
        grid-template-columns: 1fr;
    }

    .table-section {
        border-radius: 16px;
    }

    .actions-cell,
    .actions-heading {
        width: auto;
    }
}

</style>
