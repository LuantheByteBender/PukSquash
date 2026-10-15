<script setup>
/**
 * ================================================
 * Global imports
 * ================================================
 */
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

/**
 * ================================================
 * Local imports
 * ================================================
 */
import { http } from '../services/http.js';
import LoadingSpinner from '../components/LoadingSpinner.vue';

/**
 * ================================================
 * State
 * ================================================
 */
const router = useRouter();

const payments = ref([]);
const isLoading = ref(false);
const loadError = ref('');

const search = ref('');
const statusFilter = ref('');

const statusOptions = ['PENDING', 'PAID', 'FAILED', 'CANCELLED'];

function statusPillVariant(status) {
    if (status === 'PAID') return 'pill-green';
    if (status === 'PENDING') return 'pill-amber';
    if (status === 'FAILED') return 'pill-red';
    return 'pill-grey';
}

function formatAmount(cents, currency) {
    const amount = (cents / 100).toFixed(2);
    return `${currency ?? 'ZAR'} ${amount}`;
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('en-ZA', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function normalizePayment(record) {
    return {
        id: record.id ?? '',
        fullName: record.full_name ?? '-',
        email: record.email ?? '-',
        studentNumber: record.student_number ?? '-',
        phoneNumber: record.phone_number ?? '-',
        provider: record.provider ?? '-',
        amountCents: record.amount_cents ?? 0,
        currency: record.currency ?? 'ZAR',
        status: record.status ?? 'UNKNOWN',
        paidAt: record.paid_at ?? null,
        createdAt: record.created_at ?? null,
        merchantPaymentId: record.merchant_payment_id ?? '-',
        payfastPaymentId: record.payfast_payment_id ?? '-',
    };
}

const filteredPayments = computed(() => {
    const query = search.value.trim().toLowerCase();

    return payments.value.filter((p) => {
        const matchesQuery =
            !query ||
            p.fullName.toLowerCase().includes(query) ||
            p.email.toLowerCase().includes(query) ||
            (p.studentNumber || '').toLowerCase().includes(query) ||
            p.status.toLowerCase().includes(query);

        const matchesStatus = !statusFilter.value || p.status === statusFilter.value;

        return matchesQuery && matchesStatus;
    });
});

async function loadPayments() {
    isLoading.value = true;
    loadError.value = '';

    try {
        const response = await http.get('/admin/registration-payments');
        const payload = Array.isArray(response) ? response : [];
        payments.value = payload.map(normalizePayment);
    } catch (error) {
        console.error('Failed to load registration payments:', error);
        loadError.value = 'Unable to load payment records right now. Please try again.';
        payments.value = [];
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    loadPayments();
});
</script>

<template>
    <main class="page registration-payments-page">

        <header class="page-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h1 class="page-title">Registration Payments</h1>
            </div>

            <button type="button" class="btn btn-secondary" @click="router.push('/admin/dashboard')">
                Back to Dashboard
            </button>
        </header>

        <p v-if="loadError" class="alert alert-error">{{ loadError }}</p>

        <section class="card">
            <div class="row table-controls">
                <label class="field grow">
                    <span class="label">Search</span>
                    <input v-model="search" type="text" placeholder="Name, email, student number..." />
                </label>

                <label class="field">
                    <span class="label">Status</span>
                    <select v-model="statusFilter">
                        <option value="">All statuses</option>
                        <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                    </select>
                </label>
            </div>

            <p v-if="isLoading" class="muted">Loading payment records...</p>

            <div class="table-wrap" v-else>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Student #</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Provider</th>
                            <th scope="col">Status</th>
                            <th scope="col">Initiated</th>
                            <th scope="col">Paid at</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="payment in filteredPayments" :key="payment.id">
                            <td>
                                <strong>{{ payment.fullName }}</strong>
                            </td>
                            <td class="muted">{{ payment.email }}</td>
                            <td class="muted">{{ payment.studentNumber }}</td>
                            <td>{{ formatAmount(payment.amountCents, payment.currency) }}</td>
                            <td class="muted">{{ payment.provider }}</td>
                            <td>
                                <span class="pill" :class="statusPillVariant(payment.status)">
                                    {{ payment.status }}
                                </span>
                            </td>
                            <td class="muted">{{ formatDate(payment.createdAt) }}</td>
                            <td class="muted">{{ formatDate(payment.paidAt) }}</td>
                        </tr>

                        <tr v-if="filteredPayments.length === 0 && !isLoading">
                            <td colspan="8" class="muted empty">No payment records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</template>

<style scoped>
.registration-payments-page {
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
    max-height: 540px;
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    background: var(--ps-surface);
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.payments-table th {
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

.payments-table td {
    padding: 0.75rem 0.75rem;
    border-bottom: 1px solid var(--ps-border-soft);
    vertical-align: middle;
    font-size: 0.875rem;
}

.payments-table tbody tr {
    transition: background 0.12s ease;
}

.payments-table tbody tr:hover {
    background: rgba(108, 62, 148, 0.03);
}

.empty {
    padding: 1.5rem 0.75rem;
    text-align: center;
}

@media (max-width: 760px) {
    .registration-payments-page {
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
}
</style>
