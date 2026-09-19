<script setup>
import { computed, onMounted, ref } from 'vue';
import { refreshAccount, updateAccount, useAccount } from '../composables/useAccount.js';

const isEditing = ref(false);
const saved = ref(false);
const confirmingSave = ref(false);

const { profile: accountProfile } = useAccount();
const error = ref('');
const saving = ref(false);

const profile = ref({
    firstName: '',
    lastName: '',
    studentNumber: '',
    email: '',
    role: '',
});

const initials = computed(() => {
    const first = (profile.value.firstName || '').trim();
    const last = (profile.value.lastName || '').trim();

    const a = first ? first[0] : '';
    const b = last ? last[0] : '';

    const out = (a + b).toUpperCase();
    return out || 'P';
});

const roleLabel = computed(() => profile.value.role || 'USER');

function applyFromAccount(next) {
    if (!next) return;

    const full = String(next.full_name ?? '').trim();
    const parts = full.split(/\s+/).filter(Boolean);

    profile.value.firstName = parts[0] ?? full;
    profile.value.lastName = parts.slice(1).join(' ');
    profile.value.studentNumber = String(next.student_number ?? '');
    profile.value.email = String(next.email ?? '');
    profile.value.role = String(next.role ?? '');
}

onMounted(async () => {
    error.value = '';

    try {
        const next = await refreshAccount();
        applyFromAccount(next);
    } catch (e) {
        error.value = e?.message ?? String(e);
    }
});

const editProfile = () => {
    saved.value = false;
    isEditing.value = true;
};

const cancelEdit = () => {
    applyFromAccount(accountProfile.value);
    isEditing.value = false;
};

const requestSave = () => {
    if (saving.value) return;
    confirmingSave.value = true;
};

const saveProfile = async () => {
    error.value = '';
    confirmingSave.value = false;
    saving.value = true;

    try {
        const fullName = `${profile.value.firstName} ${profile.value.lastName}`.trim();
        await updateAccount({
            full_name: fullName,
            student_number: accountProfile.value?.student_number
                ?? accountProfile.value?.studentNumber
                ?? null,
        });

        applyFromAccount(accountProfile.value);
        isEditing.value = false;
        saved.value = true;
        setTimeout(() => { saved.value = false; }, 3000);
    } catch (e) {
        error.value = e?.message ?? String(e);
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="profile-page">

        <!-- Main content -->
        <main class="profile-main">

            <header class="page-header">
                <div class="page-header__left">
                    <!-- <div class="avatar-ring">
                        <div class="avatar">{{ initials }}</div>
                    </div> -->

                    <div class="page-header__text">
                        <h1 class="page-title page-title--lg">My profile</h1>

                        <div class="page-badges">
                            <span class="badge badge--role">{{ roleLabel }}</span>
                            <span v-if="profile.studentNumber" class="badge badge--id">
                                #{{ profile.studentNumber }}
                            </span>
                        </div>
                    </div>
                </div>

                <button
                    v-if="!isEditing"
                    type="button"
                    class="edit-btn"
                    @click="editProfile"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    Edit
                </button>
            </header>

            <!-- Feedback banners -->
            <div v-if="error" class="banner banner--error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                {{ error }}
            </div>

            <div v-if="saved" class="banner banner--success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                Profile updated successfully
            </div>

            <!-- Editable card -->
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Personal information</h2>
                    <p class="card-subtitle">Update your name as it should appear on bookings.</p>
                </div>

                <div class="card-body">
                    <div class="field-grid">
                        <div class="field">
                            <label class="field-label" for="profile-first">First name</label>
                            <input
                                id="profile-first"
                                v-model="profile.firstName"
                                :disabled="!isEditing"
                                type="text"
                                class="field-input"
                                :class="{ 'field-input--active': isEditing }"
                                placeholder="First name"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="profile-last">Last name</label>
                            <input
                                id="profile-last"
                                v-model="profile.lastName"
                                :disabled="!isEditing"
                                type="text"
                                class="field-input"
                                :class="{ 'field-input--active': isEditing }"
                                placeholder="Last name"
                            />
                        </div>
                    </div>

                    <div class="details">
                        <h3 class="details-title">Account details</h3>

                        <div class="details-grid">
                            <div class="detail">
                                <span class="detail-label">Email</span>
                                <span class="detail-value">{{ profile.email || '—' }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Student number</span>
                                <span class="detail-value">{{ profile.studentNumber || '—' }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Role</span>
                                <span class="detail-value">{{ roleLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons (only while editing) -->
                    <div v-if="isEditing" class="card-actions">
                        <button type="button" class="btn btn--ghost" @click="cancelEdit">
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="btn btn--primary"
                            :disabled="saving"
                            @click="requestSave"
                        >
                            <svg v-if="saving" class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ saving ? 'Saving…' : 'Save changes' }}
                        </button>
                    </div>
                </div>
            </section>

        </main>

        <div
            v-if="confirmingSave"
            class="modal-backdrop"
            @click.self="confirmingSave = false"
        >
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="save-title">
                <h2 id="save-title" class="modal-title">Save changes?</h2>

                <p class="modal-text">
                    This will update your name to
                    <strong>{{ profile.firstName }} {{ profile.lastName }}</strong>.
                </p>

                <div class="modal-actions">
                    <button type="button" class="btn btn--ghost" @click="confirmingSave = false">
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="btn btn--primary"
                        :disabled="saving"
                        @click="confirmingSave = false; saveProfile();"
                    >
                        {{ saving ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Page ────────────────────────────────────────── */
.profile-page {
    min-height: 100vh;
    background: #f8f7fb;
}

.avatar-ring {
    padding: 3px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255,255,255,.35), rgba(255,255,255,.1));
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #fff;
    color: #581c87;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: .02em;
}

/* ── Page header ─────────────────────────────────── */
.page-header {
    background: #fff;
    border: 1px solid #ede9f4;
    border-radius: 0.85rem;
    padding: 1.15rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.page-header__left {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

.page-header__text {
    min-width: 0;
}

.page-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1e1b2e;
}

.page-title--lg {
    font-size: 1.35rem;
}

.page-subtitle {
    margin: 0.15rem 0 0;
    font-size: 0.9rem;
    font-weight: 650;
    color: #4b3b6b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.55rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: .2rem .7rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.badge--role {
    background: rgba(255,255,255,.18);
    color: #fff;
}

.badge--id {
    background: rgba(255,255,255,.1);
    color: #e9d5ff;
}

.edit-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .55rem 1.1rem;
    border: 1.5px solid rgba(255,255,255,.3);
    border-radius: .6rem;
    background: rgba(126, 34, 206, 0.06);
    border-color: rgba(126, 34, 206, 0.25);
    color: #581c87;
    font-size: .82rem;
    font-weight: 650;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
}

.edit-btn:hover {
    background: rgba(126, 34, 206, 0.1);
    border-color: rgba(126, 34, 206, 0.4);
}

/* ── Main ────────────────────────────────────────── */
.profile-main {
    max-width: 52rem;
    margin: 0 auto;
    padding: 1.5rem 1.5rem 3rem;
}

/* ── Banners ─────────────────────────────────────── */
.banner {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .7rem 1rem;
    border-radius: .6rem;
    font-size: .85rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
}

.banner--error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.banner--success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* ── Card ────────────────────────────────────────── */
.card {
    background: #fff;
    border: 1px solid #ede9f4;
    border-radius: .85rem;
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f0f7;
}

.card-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 750;
    color: #1e1b2e;
}

.card-subtitle {
    margin: .25rem 0 0;
    font-size: .82rem;
    color: #6b7280;
}

.card-body {
    padding: 1.5rem;
}

.details {
    margin-top: 1.35rem;
    padding-top: 1.35rem;
    border-top: 1px solid #f3f0f7;
}

.details-title {
    margin: 0 0 0.9rem;
    font-size: 0.9rem;
    font-weight: 750;
    color: #1e1b2e;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 0.85rem;
}

.detail {
    background: #faf9fc;
    border: 1px solid #e5e1ee;
    border-radius: 0.7rem;
    padding: 0.9rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9061c2;
}

.detail-value {
    font-size: 0.92rem;
    font-weight: 650;
    color: #1e1b2e;
    word-break: break-word;
}

.card-actions {
    display: flex;
    justify-content: flex-end;
    gap: .6rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #f3f0f7;
}

/* ── Fields ──────────────────────────────────────── */
.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 500px) {
    .field-grid { grid-template-columns: 1fr; }
}

.field-label {
    display: block;
    margin-bottom: .4rem;
    font-size: .78rem;
    font-weight: 700;
    color: #4b3b6b;
}

.field-input {
    width: 100%;
    padding: .65rem .9rem;
    border: 1.5px solid #e5e1ee;
    border-radius: .55rem;
    font-size: .9rem;
    font-weight: 500;
    color: #1e1b2e;
    background: #faf9fc;
    transition: border-color .15s, background .15s, box-shadow .15s;
    outline: none;
}

.field-input:disabled {
    background: #faf9fc;
    color: #6b7280;
    cursor: default;
}

.field-input--active {
    background: #fff;
    border-color: #c4b5fd;
}

.field-input--active:focus {
    border-color: #7e22ce;
    box-shadow: 0 0 0 3px rgba(126, 34, 206, .12);
}

/* ── Buttons ─────────────────────────────────────── */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    padding: .6rem 1.3rem;
    border-radius: .55rem;
    font-size: .85rem;
    font-weight: 650;
    cursor: pointer;
    transition: background .15s, box-shadow .15s, opacity .15s;
    border: none;
    white-space: nowrap;
}

.btn--ghost {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e1ee;
}

.btn--ghost:hover {
    background: #f9f8fb;
}

.btn--primary {
    background: linear-gradient(135deg, #7e22ce, #581c87);
    color: #fff;
    box-shadow: 0 1px 3px rgba(88, 28, 135, .25);
}

.btn--primary:hover {
    box-shadow: 0 3px 10px rgba(88, 28, 135, .3);
}

.btn--primary:disabled {
    opacity: .6;
    cursor: not-allowed;
}

/* ── Save confirmation modal ─────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 60;
    display: grid;
    place-items: center;
    padding: 1rem;
    background: rgba(24, 14, 33, 0.55);
}

.modal {
    width: 100%;
    max-width: 460px;
    background: #fff;
    border-radius: 0.9rem;
    border: 1px solid #ede9f4;
    box-shadow: 0 20px 50px rgba(24, 14, 33, 0.25);
    padding: 1.25rem 1.25rem 1.1rem;
}

.modal-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1e1b2e;
}

.modal-text {
    margin: 0.6rem 0 0;
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.4;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 1.1rem;
}

/* ── Spinner ─────────────────────────────────────── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .7s linear infinite; }
</style>