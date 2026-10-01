<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import LoadingSpinner from '../components/LoadingSpinner.vue';
import { http } from '../services/http.js';
import { useToast } from '../composables/useToast.js';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const router = useRouter();
const toast = useToast();

// Stores user data, loading state , filters and editing state
const users = ref([]);
const isLoading = ref(false);
const loadError = ref('');
const actionError = ref('');
const isWorking = ref(false);
const actionKey = ref('');

const confirmState = ref({
    open: false,
    title: 'Confirm',
    message: '',
    confirmText: 'Confirm',
    confirmVariant: 'primary',
    resolve: null,
});

function requestConfirm({ title, message, confirmText, confirmVariant }) {
    confirmState.value.open = true;
    confirmState.value.title = title ?? 'Confirm';
    confirmState.value.message = message ?? '';
    confirmState.value.confirmText = confirmText ?? 'Confirm';
    confirmState.value.confirmVariant = confirmVariant ?? 'primary';

    return new Promise((resolve) => {
        confirmState.value.resolve = resolve;
    });
}

function resolveConfirm(value) {
    const resolve = confirmState.value.resolve;
    confirmState.value.open = false;
    confirmState.value.resolve = null;
    if (typeof resolve === 'function') {
        resolve(value);
    }
}
const search = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const editingUserId = ref(null);
const draftRole = ref('USER');
const draftStatus = ref('ACTIVE');

// Available options for user roles and statuses
const roleOptions = ['USER', 'COACH', 'ADMIN'];
const statusOptions = ['ACTIVE', 'INACTIVE', 'SUSPENDED'];

function rolePillVariant(role) {
    if (role === 'ADMIN') return '';
    if (role === 'COACH') return 'pill-amber';
    return 'pill-grey';
}

function statusPillVariant(status) {
    if (status === 'ACTIVE') return '';
    if (status === 'SUSPENDED') return 'pill-red';
    return 'pill-grey';
}

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
        phoneNumber: user.phoneNumber ?? user.phone_number ?? '',
        role: user.role || 'USER',
        status: user.status || 'ACTIVE',
    };
}

function isActionBusy(key) {
    return isWorking.value && actionKey.value === key;
}


// Loads users from the backend API and normalizes the data for consistent structure
async function loadUsers() {
    isLoading.value = true;
    loadError.value = '';
    actionError.value = '';

    try {
        const response = await http.get('/admin/users');
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
            (user.phoneNumber || '').toLowerCase().includes(query) ||
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

async function saveUser(user) {
    actionError.value = '';

    if (isWorking.value) return;

    const nextRole = draftRole.value;
    const nextStatus = draftStatus.value;

    if (nextStatus !== user.status) {
        const ok = await requestConfirm({
            title: 'Confirm status change',
            message: `Change status for "${user.fullName}" from ${user.status} to ${nextStatus}?`,
            confirmText: 'Confirm',
        });
        if (!ok) return;
    }

    if (nextRole !== user.role) {
        const ok = await requestConfirm({
            title: 'Confirm role change',
            message: `Change role for "${user.fullName}" from ${user.role} to ${nextRole}?`,
            confirmText: 'Confirm',
        });
        if (!ok) return;
    }

    isWorking.value = true;
    actionKey.value = `save:${user.id}`;

    try {
        const updated = await http.put(`/admin/users/${user.id}`, {
            role: nextRole,
            status: nextStatus,
        });

        const normalized = normalizeUser(updated ?? user);
        users.value = users.value.map((row) => (row.id === user.id ? normalized : row));
        cancelEdit();
        toast.success('User updated.');
    } catch (error) {
        console.error('Failed to update user:', error);
        actionError.value = 'Unable to save changes. Please try again.';
        toast.error(actionError.value);
    } finally {
        isWorking.value = false;
        actionKey.value = '';
    }
}

async function removeUser(id) {
    actionError.value = '';

    if (isWorking.value) return;

    const user = users.value.find((u) => u.id === id);
    const who = user?.fullName || 'this user';

    const ok = await requestConfirm({
        title: 'Delete user?',
        message: `Permanently delete ${who}? This cannot be undone.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });
    if (!ok) return;

    isWorking.value = true;
    actionKey.value = `delete:${id}`;

    try {
        await http.delete(`/admin/users/${id}`);
        users.value = users.value.filter((user) => user.id !== id);
        toast.success('User deleted.');
    } catch (error) {
        console.error('Failed to delete user:', error);
        actionError.value = error?.message || 'Unable to remove user. Please try again.';
        toast.error(actionError.value);
        return;
    } finally {
        isWorking.value = false;
        actionKey.value = '';
    }

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

            <button type="button" class="btn btn-secondary" @click="router.push('/admin/dashboard')">
                Back to Dashboard
            </button>
        </header>

        <p v-if="loadError" class="alert alert-error">{{ loadError }}</p>
        <p v-else-if="actionError" class="alert alert-error">{{ actionError }}</p>

        <section class="card">
            <div class="row table-controls">
                <label class="field grow">
                    <span class="label">User name</span>
                    <input v-model="search" type="text" placeholder="Search users..." />
                </label>

                <label class="field">
                    <span class="label">Role</span>
                    <select v-model="roleFilter">
                        <option value="">All roles</option>
                        <option v-for="role in roleOptions" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </label>

                <label class="field">
                    <span class="label">Status</span>
                    <select v-model="statusFilter">
                        <option value="">All statuses</option>
                        <option v-for="status in statusOptions" :key="status" :value="status">
                            {{ status }}
                        </option>
                    </select>
                </label>
            </div>

            <p v-if="isLoading" class="muted">Loading users...</p>

            <div class="table-wrap" v-else>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone number</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="actions">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td>
                                <strong>{{ user.fullName }}</strong>
                            </td>
                            <td class="muted">{{ user.email }}</td>
                            <td class="muted">{{ user.phoneNumber || '-' }}</td>
                            <td>
                                <template v-if="editingUserId === user.id">
                                    <select v-model="draftRole" :disabled="isWorking">
                                        <option v-for="role in roleOptions" :key="role" :value="role">
                                            {{ role }}
                                        </option>
                                    </select>
                                </template>
                                <template v-else>
                                    <span class="pill" :class="rolePillVariant(user.role)">{{ user.role }}</span>
                                </template>
                            </td>
                            <td>
                                <template v-if="editingUserId === user.id">
                                    <select v-model="draftStatus" :disabled="isWorking">
                                        <option v-for="status in statusOptions" :key="status" :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                </template>
                                <template v-else>
                                    <span class="pill" :class="statusPillVariant(user.status)">
                                        {{ user.status }}
                                    </span>
                                </template>
                            </td>
                            <td class="actions">
                                <template v-if="editingUserId === user.id">
                                    <button type="button" class="btn btn-secondary btn-sm" :disabled="isWorking" @click="saveUser(user)">
                                        <LoadingSpinner v-if="isActionBusy(`save:${user.id}`)" quiet />
                                        Save
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" :disabled="isWorking" @click="cancelEdit">
                                        Cancel
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" class="btn btn-secondary btn-sm" :disabled="isWorking" @click="startEdit(user)">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" :disabled="isWorking" @click="removeUser(user.id)">
                                        <LoadingSpinner v-if="isActionBusy(`delete:${user.id}`)" quiet />
                                        Delete
                                    </button>
                                </template>
                            </td>
                        </tr>

                        <tr v-if="filteredUsers.length === 0 && !isLoading">
                            <td colspan="6" class="muted empty">
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <ConfirmDialog
        :open="confirmState.open"
        :title="confirmState.title"
        :message="confirmState.message"
        :confirm-text="confirmState.confirmText"
        :confirm-variant="confirmState.confirmVariant"
        @confirm="resolveConfirm(true)"
        @cancel="resolveConfirm(false)"
    />
</template>

<style scoped>
.manage-users-page {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
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
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ps-brand);
}

.page-title {
    margin: 0.2rem 0 0;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 800;
    letter-spacing: -0.02em;
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
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ps-text-muted);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.table-wrap {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 520px;
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    background: var(--ps-surface);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.users-table th {
    text-align: left;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--ps-text-muted);
    padding: 0.75rem 0.75rem;
    border-bottom: 1px solid var(--ps-border-soft);
    background: rgba(242, 236, 247, 0.3);
    position: sticky;
    top: 0;
    z-index: 2;
}

.users-table td {
    padding: 0.75rem 0.75rem;
    border-bottom: 1px solid var(--ps-border-soft);
    vertical-align: middle;
    font-size: 0.875rem;
}

.users-table tbody tr {
    transition: background 0.12s ease;
}

.users-table tbody tr:hover {
    background: rgba(108, 62, 148, 0.03);
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
    padding: 1.5rem 0.75rem;
    text-align: center;
}

@media (max-width: 760px) {
    .manage-users-page {
        gap: 1.25rem;
        padding-top: 1rem;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-title {
        font-size: clamp(1.6rem, 8vw, 2rem);
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
