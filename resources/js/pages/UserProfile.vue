<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { friendlyAuthError } from '../features/auth/authService.js';
import { refreshAccount, updateAccount, clearAccount, useAccount } from '../composables/useAccount.js';

const router = useRouter();

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
    phoneNumber: '',
    email: '',
    role: '',
    registeredAt: '',
});

const emailUpdate = ref({
    nextEmail: '',
    currentPassword: '',
    working: false,
    saved: false,
    error: '',
});

const passwordUpdate = ref({
    currentPassword: '',
    nextPassword: '',
    confirmPassword: '',
    working: false,
    saved: false,
    error: '',
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

const registeredLabel = computed(() => {
    const raw = profile.value.registeredAt;
    if (!raw) return '—';
    const d = new Date(raw);
    if (isNaN(d.getTime())) return raw;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
});

function applyFromAccount(next) {
    if (!next) return;

    const full = String(next.full_name ?? '').trim();
    const parts = full.split(/\s+/).filter(Boolean);

    profile.value.firstName = parts[0] ?? full;
    profile.value.lastName = parts.slice(1).join(' ');
    profile.value.studentNumber = String(next.student_number ?? '');
    profile.value.phoneNumber = String(next.phone_number ?? '');
    profile.value.email = String(next.email ?? '');
    profile.value.role = String(next.role ?? '');
    profile.value.registeredAt = String(next.created_at ?? '');
}

async function reauthenticate(currentPassword) {
    const { auth } = await import('../services/firebase.js');
    const user = auth?.currentUser;

    if (!user) {
        throw new Error('You are not signed in.');
    }

    const [{ EmailAuthProvider, reauthenticateWithCredential }] = await Promise.all([
        import('firebase/auth'),
    ]);

    const credential = EmailAuthProvider.credential(
        user.email ?? profile.value.email,
        String(currentPassword ?? '')
    );

    await reauthenticateWithCredential(user, credential);

    return user;
}

async function syncProfileFromBackend() {
    const next = await refreshAccount();
    applyFromAccount(next);
}

async function updateEmailAddress() {
    emailUpdate.value.error = '';
    emailUpdate.value.saved = false;

    const nextEmail = String(emailUpdate.value.nextEmail ?? '').trim();
    const currentEmail = String(profile.value.email ?? '').trim();
    if (!nextEmail) {
        emailUpdate.value.error = 'Please enter a new email address.';
        return;
    }

    if (!/^\S+@\S+\.\S+$/.test(nextEmail)) {
        emailUpdate.value.error = 'Please enter a valid email address.';
        return;
    }

    if (currentEmail && nextEmail.toLowerCase() === currentEmail.toLowerCase()) {
        emailUpdate.value.error = 'Please enter a different email address.';
        return;
    }

    if (!emailUpdate.value.currentPassword) {
        emailUpdate.value.error = 'Please enter your current password to confirm.';
        return;
    }

    emailUpdate.value.working = true;

    try {
        const user = await reauthenticate(emailUpdate.value.currentPassword);
        const { verifyBeforeUpdateEmail, signOut } = await import('firebase/auth');

        await verifyBeforeUpdateEmail(user, nextEmail);

        emailUpdate.value.currentPassword = '';
        emailUpdate.value.saved = true;

        const { auth } = await import('../services/firebase.js');
        clearAccount();
        await signOut(auth);

        await router.push({ name: 'login' });
    } catch (e) {
        emailUpdate.value.error = friendlyAuthError(e);
    } finally {
        emailUpdate.value.working = false;
    }
}

async function updatePasswordValue() {
    passwordUpdate.value.error = '';
    passwordUpdate.value.saved = false;

    if (!passwordUpdate.value.currentPassword) {
        passwordUpdate.value.error = 'Please enter your current password.';
        return;
    }

    if (!passwordUpdate.value.nextPassword) {
        passwordUpdate.value.error = 'Please enter a new password.';
        return;
    }

    if (String(passwordUpdate.value.nextPassword).length < 8) {
        passwordUpdate.value.error = 'Your new password must be at least 8 characters.';
        return;
    }

    if (passwordUpdate.value.currentPassword === passwordUpdate.value.nextPassword) {
        passwordUpdate.value.error = 'Your new password must be different from your current password.';
        return;
    }

    if (!passwordUpdate.value.confirmPassword) {
        passwordUpdate.value.error = 'Please confirm your new password.';
        return;
    }

    if (passwordUpdate.value.nextPassword !== passwordUpdate.value.confirmPassword) {
        passwordUpdate.value.error = 'Passwords do not match.';
        return;
    }

    passwordUpdate.value.working = true;

    try {
        const user = await reauthenticate(passwordUpdate.value.currentPassword);
        const { updatePassword } = await import('firebase/auth');

        await updatePassword(user, passwordUpdate.value.nextPassword);

        passwordUpdate.value.currentPassword = '';
        passwordUpdate.value.nextPassword = '';
        passwordUpdate.value.confirmPassword = '';

        passwordUpdate.value.saved = true;
        setTimeout(() => { passwordUpdate.value.saved = false; }, 3000);
    } catch (e) {
        passwordUpdate.value.error = friendlyAuthError(e);
    } finally {
        passwordUpdate.value.working = false;
    }
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
            student_number: profile.value.studentNumber || null,
            phone_number: profile.value.phoneNumber || null,
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
                    <div class="details">
                        <h3 class="details-title">Account details</h3>

                        <div class="details-grid">
                            <div class="detail">
                                <span class="detail-label">Name</span>
                                <span class="detail-value">{{ profile.firstName }} {{ profile.lastName }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Email</span>
                                <span class="detail-value">{{ profile.email || '—' }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Student number</span>
                                <span class="detail-value">{{ profile.studentNumber || '—' }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value">{{ profile.phoneNumber || '—' }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Role</span>
                                <span class="detail-value">{{ roleLabel }}</span>
                            </div>

                            <div class="detail">
                                <span class="detail-label">Registered</span>
                                <span class="detail-value">{{ registeredLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="isEditing" class="field-grid" style="margin-top: 1rem;">
                        <div class="field">
                            <label class="field-label" for="profile-first">First name</label>
                            <input
                                id="profile-first"
                                v-model="profile.firstName"
                                type="text"
                                class="field-input field-input--active"
                                placeholder="First name"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="profile-last">Last name</label>
                            <input
                                id="profile-last"
                                v-model="profile.lastName"
                                type="text"
                                class="field-input field-input--active"
                                placeholder="Last name"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="profile-student">Student number</label>
                            <input
                                id="profile-student"
                                v-model="profile.studentNumber"
                                type="text"
                                class="field-input field-input--active"
                                placeholder="Student number"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="profile-phone">Phone number</label>
                            <input
                                id="profile-phone"
                                v-model="profile.phoneNumber"
                                type="tel"
                                class="field-input field-input--active"
                                placeholder="Phone number"
                            />
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

            <section class="card" style="margin-top: 1rem;">
                <div class="card-header">
                    <h2 class="card-title">Sign-in details</h2>
                    <p class="card-subtitle">Update your email address and password.</p>
                </div>

                <div class="card-body">
                    <div v-if="emailUpdate.error" class="banner banner--error">
                        {{ emailUpdate.error }}
                    </div>

                    <div v-if="emailUpdate.saved" class="banner banner--success">
                        A verification email has been sent to your new address. Please verify it and sign in again.
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label class="field-label" for="email-next">New email</label>
                            <input
                                id="email-next"
                                v-model="emailUpdate.nextEmail"
                                type="email"
                                class="field-input field-input--active"
                                placeholder="name@example.com"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="email-current">Current password</label>
                            <input
                                id="email-current"
                                v-model="emailUpdate.currentPassword"
                                type="password"
                                class="field-input field-input--active"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div class="card-actions">
                        <button
                            type="button"
                            class="btn btn--primary"
                            :disabled="emailUpdate.working"
                            @click="updateEmailAddress"
                        >
                            {{ emailUpdate.working ? 'Updating…' : 'Update email' }}
                        </button>
                    </div>

                    <div style="margin-top: 1.25rem;"></div>

                    <div v-if="passwordUpdate.error" class="banner banner--error">
                        {{ passwordUpdate.error }}
                    </div>

                    <div v-if="passwordUpdate.saved" class="banner banner--success">
                        Password updated successfully
                    </div>

                    <div class="field-stack">
                        <div class="field">
                            <label class="field-label" for="pw-current">Current password</label>
                            <input
                                id="pw-current"
                                v-model="passwordUpdate.currentPassword"
                                type="password"
                                class="field-input field-input--active"
                                placeholder="••••••••"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="pw-next">New password</label>
                            <input
                                id="pw-next"
                                v-model="passwordUpdate.nextPassword"
                                type="password"
                                class="field-input field-input--active"
                                placeholder="••••••••"
                            />
                        </div>

                        <div class="field">
                            <label class="field-label" for="pw-confirm">Confirm new password</label>
                            <input
                                id="pw-confirm"
                                v-model="passwordUpdate.confirmPassword"
                                type="password"
                                class="field-input field-input--active"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div class="card-actions">
                        <button
                            type="button"
                            class="btn btn--primary"
                            :disabled="passwordUpdate.working"
                            @click="updatePasswordValue"
                        >
                            {{ passwordUpdate.working ? 'Updating…' : 'Update password' }}
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
    background: transparent;
    animation: profile-in 0.3s ease;
}

@keyframes profile-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
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
    background: var(--ps-surface);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-lg, 16px);
    padding: 1.25rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--ps-shadow-sm);
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
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ps-text);
    letter-spacing: -0.01em;
}

.page-title--lg {
    font-size: 1.4rem;
    letter-spacing: -0.02em;
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
    background: var(--ps-brand-tint);
    color: var(--ps-brand);
}

.badge--id {
    background: #ede5f5;
    color: #5d3a7a;
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
    background: var(--ps-surface);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-lg, 16px);
    overflow: hidden;
    box-shadow: var(--ps-shadow-sm);
    transition: box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.card:hover {
    box-shadow: var(--ps-shadow);
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f0f7;
}

.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 750;
    color: var(--ps-text);
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
    margin-top: 0;
    padding-top: 0;
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
    background: linear-gradient(135deg, rgba(242, 236, 247, 0.6) 0%, rgba(242, 236, 247, 0.9) 100%);
    border: 1px solid rgba(221, 207, 233, 0.5);
    border-radius: var(--ps-radius);
    padding: 0.9rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.detail:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(108, 62, 148, 0.1);
    border-color: rgba(108, 62, 148, 0.2);
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

.field-stack {
    display: flex;
    flex-direction: column;
    gap: 1rem;
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
    border: 1.5px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-sm);
    font-size: .875rem;
    font-weight: 500;
    color: var(--ps-text);
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
    border-color: var(--ps-brand);
    box-shadow: 0 0 0 3px rgba(108, 62, 148, .12);
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
    background: rgba(23, 18, 29, 0.55);
    backdrop-filter: blur(4px);
    animation: prof-fade 0.2s ease;
}

.modal {
    width: 100%;
    max-width: 460px;
    background: var(--ps-surface-elevated, #fff);
    border-radius: var(--ps-radius-lg, 16px);
    border: 1px solid var(--ps-border-soft);
    box-shadow: var(--ps-shadow-xl);
    padding: 1.5rem;
    animation: prof-scale 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ps-text);
}

.modal-text {
    margin: 0.6rem 0 0;
    font-size: 0.875rem;
    color: var(--ps-text-muted);
    line-height: 1.45;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 1.25rem;
}

@keyframes prof-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes prof-scale {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}

/* ── Spinner ─────────────────────────────────────── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .7s linear infinite; }
</style>