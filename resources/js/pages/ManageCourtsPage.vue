<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import { http } from '../services/http.js';
import { useToast } from '../composables/useToast.js';

const router = useRouter();
const toast = useToast();

const courts = ref([]);
const isLoading = ref(false);
const loadError = ref('');
const actionError = ref('');
const isWorking = ref(false);

const editingCourtId = ref(null);
const draftName = ref('');
const draftDescription = ref('');

const search = ref('');
const statusFilter = ref('');

const nameDraft = ref('');
const statusDraft = ref('ACTIVE');

const statusOptions = ['ACTIVE', 'INACTIVE'];

function normalizeCourt(court) {
    return {
        id: court.id,
        name: court.name ?? 'Unknown court',
        description: court.description ?? '',
        status: court.status ?? 'INACTIVE',
    };
}

function startEdit(court) {
    editingCourtId.value = court.id;
    draftName.value = court.name;
    draftDescription.value = court.description ?? '';
}

function cancelEdit() {
    editingCourtId.value = null;
    draftName.value = '';
    draftDescription.value = '';
}

async function saveCourt(court) {
    actionError.value = '';

    if (isWorking.value) return;
    isWorking.value = true;

    try {
        const updated = await http.put(`/admin/courts/${court.id}`, {
            name: draftName.value.trim(),
            description: draftDescription.value.trim() || null,
        });

        const normalized = normalizeCourt(updated ?? { ...court, name: draftName.value, description: draftDescription.value });
        courts.value = courts.value.map((row) => (row.id === court.id ? normalized : row));
        cancelEdit();
        toast.success('Court updated.');
    } catch (error) {
        console.error('Failed to update court:', error);
        actionError.value = 'Unable to update court. Please try again.';
        toast.error(actionError.value);
    } finally {
        isWorking.value = false;
    }
}

async function loadCourts() {
    isLoading.value = true;
    loadError.value = '';
    actionError.value = '';

    try {
        const response = await http.get('/admin/courts');
        const payload = Array.isArray(response)
            ? response
            : Array.isArray(response?.courts)
                ? response.courts
                : [];

        courts.value = payload.map(normalizeCourt);
    } catch (error) {
        console.error('Failed to load courts:', error);
        loadError.value = 'Unable to load courts right now. Please try again.';
        courts.value = [];
    } finally {
        isLoading.value = false;
    }
}

const filteredCourts = computed(() => {
    const query = search.value.trim().toLowerCase();

    return courts.value.filter((court) => {
        const matchesQuery = !query || court.name.toLowerCase().includes(query);
        const matchesStatus = !statusFilter.value || court.status === statusFilter.value;

        return matchesQuery && matchesStatus;
    });
});

async function createCourt() {
    actionError.value = '';

    const name = nameDraft.value.trim();
    if (!name) {
        actionError.value = 'Please provide a court name.';
        return;
    }

    if (isWorking.value) return;
    isWorking.value = true;

    try {
        await http.post('/admin/courts', {
            name,
            status: statusDraft.value,
        });

        nameDraft.value = '';
        statusDraft.value = 'ACTIVE';
        await loadCourts();
        toast.success('Court created.');
    } catch (error) {
        console.error('Failed to create court:', error);
        actionError.value = 'Unable to create court. Please try again.';
        toast.error(actionError.value);
    } finally {
        isWorking.value = false;
    }
}

async function setCourtStatus(court, status) {
    actionError.value = '';

    if (isWorking.value) return;
    isWorking.value = true;

    try {
        const updated = await http.put(`/admin/courts/${court.id}`, { status });
        const normalized = normalizeCourt(updated ?? { ...court, status });
        courts.value = courts.value.map((row) => (row.id === court.id ? normalized : row));
        toast.success(`Court ${status === 'ACTIVE' ? 'activated' : 'deactivated'}.`);
    } catch (error) {
        console.error('Failed to update court status:', error);
        actionError.value = 'Unable to update court status. Please try again.';
        toast.error(actionError.value);
    } finally {
        isWorking.value = false;
    }
}

async function deleteCourt(court) {
    actionError.value = '';

    if (isWorking.value) return;

    if (!window.confirm(`Permanently delete "${court.name}"? This cannot be undone.`)) {
        return;
    }

    isWorking.value = true;

    try {
        await http.delete(`/admin/courts/${court.id}`);
        courts.value = courts.value.filter((row) => row.id !== court.id);
        toast.success('Court deleted.');
    } catch (error) {
        console.error('Failed to delete court:', error);
        actionError.value = 'Unable to delete court. Please try again.';
        toast.error(actionError.value);
    } finally {
        isWorking.value = false;
    }
}

onMounted(() => {
    loadCourts();
});
</script>

<template>
    <main class="page manage-courts-page">
        <header class="page-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h1 class="page-title">Manage Courts</h1>
            </div>

            <button type="button" class="btn btn-secondary" @click="router.push('/admin/dashboard')">
                Back to Dashboard
            </button>
        </header>

        <p v-if="loadError" class="alert alert-error">{{ loadError }}</p>
        <p v-else-if="actionError" class="alert alert-error">{{ actionError }}</p>

        <section class="card">
            <h2 class="section-title">Add Court</h2>

            <div class="row form-row">
                <label class="field">
                    <span class="label">Court name</span>
                    <input v-model="nameDraft" type="text" placeholder="e.g. Court 1" />
                </label>

                <label class="field">
                    <span class="label">Status</span>
                    <select v-model="statusDraft">
                        <option v-for="status in statusOptions" :key="status" :value="status">
                            {{ status }}
                        </option>
                    </select>
                </label>

                <button type="button" class="btn" :disabled="isWorking" @click="createCourt">
                    {{ isWorking ? 'Working...' : 'Create' }}
                </button>
            </div>
        </section>

        <section class="card">
            <div class="row table-controls">
                <label class="field grow">
                    <span class="label">Court name</span>
                    <input v-model="search" type="text" placeholder="Search courts..." />
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

            <p v-if="isLoading" class="muted">Loading courts...</p>

            <div class="table-wrap" v-else>
                <table class="courts-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="court in filteredCourts" :key="court.id">
                            <td>
                                <template v-if="editingCourtId === court.id">
                                    <div class="edit-fields">
                                        <input v-model="draftName" type="text" />
                                        <input v-model="draftDescription" type="text" placeholder="Description (optional)" />
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="court-name">
                                        <strong>{{ court.name }}</strong>
                                        <span v-if="court.description" class="muted court-description">{{ court.description }}</span>
                                    </div>
                                </template>
                            </td>
                            <td>
                                <span class="pill" :class="court.status === 'ACTIVE' ? '' : 'pill-grey'">
                                    {{ court.status }}
                                </span>
                            </td>
                            <td class="actions">
                                <template v-if="editingCourtId === court.id">
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        :disabled="isWorking"
                                        @click="saveCourt(court)"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        :disabled="isWorking"
                                        @click="cancelEdit"
                                    >
                                        Cancel
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        :disabled="isWorking"
                                        @click="startEdit(court)"
                                    >
                                        Edit
                                    </button>

                                <button
                                    v-if="court.status !== 'ACTIVE'"
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    :disabled="isWorking"
                                    @click="setCourtStatus(court, 'ACTIVE')"
                                >
                                    Activate
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    :disabled="isWorking"
                                    @click="setCourtStatus(court, 'INACTIVE')"
                                >
                                    Deactivate
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    :disabled="isWorking"
                                    @click="deleteCourt(court)"
                                >
                                    Delete
                                </button>
                                </template>
                            </td>
                        </tr>

                        <tr v-if="filteredCourts.length === 0">
                            <td colspan="3" class="muted empty">
                                No courts found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</template>

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

.section-title {
    margin: 0 0 0.85rem;
    font-size: 1.05rem;
    font-weight: 700;
}

.form-row {
    align-items: flex-end;
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

.courts-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 560px;
}

.courts-table th {
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ps-text-muted);
    padding: 0.75rem 0.7rem;
    border-bottom: 1px solid var(--ps-border-soft);
}

.courts-table td {
    padding: 0.75rem 0.7rem;
    border-bottom: 1px solid var(--ps-border-soft);
}

.actions {
    width: 240px;
}

.courts-table td.actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.edit-fields {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    min-width: 240px;
}

.court-name {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.court-description {
    font-size: 0.85rem;
}

.empty {
    padding: 1rem 0.7rem;
}
</style>
