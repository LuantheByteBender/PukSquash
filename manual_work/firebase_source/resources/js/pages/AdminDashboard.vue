<script setup>
import { onMounted, ref } from 'vue';

import { http } from '../services/http.js';

const quickActions = ref([
    { label: 'Manage users', to: { name: 'admin-users' } },
    { label: 'Manage courts', to: { name: 'admin-courts' } },
]);

const defaultStats = [
    { label: 'Active bookings', value: 0, detail: '' },
    { label: 'Coaches registered', value: 0, detail: '' },
    { label: 'Players registered', value: 0, detail: '' },
];

const stats = ref(defaultStats.map((stat) => ({ ...stat })));

function normalizeStat(stat, index) {
    return {
        label: stat.label || stat.title || stat.name || `Stat ${index + 1}`,
        value: stat.value ?? stat.count ?? stat.total ?? '-',
        detail: stat.detail || stat.subtitle || stat.summary || '',
    };
}

async function loadStats() {
    try {
        const response = await http.get('/dashboard/stats');

        const payload = Array.isArray(response)
            ? response
            : Array.isArray(response?.stats)
                ? response.stats
                : Array.isArray(response?.data)
                    ? response.data
                    : [];

        if (payload.length > 0) {
            stats.value = payload.map(normalizeStat);
            return;
        }

        stats.value = defaultStats.map((stat) => ({ ...stat }));
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
        stats.value = defaultStats.map((stat) => ({ ...stat }));
    }
}

onMounted(() => {
    loadStats();
});
</script>

<template>
    <main class="page admin-dashboard">

        <!-- Dashboard Heading -->
        <header class="page-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h1 class="page-title">Admin dashboard</h1>
            </div>
        </header>

        <!-- Quick Action Links to Manage Pages -->
        <section class="quick-actions-section">
            <h2 class="section-title">Quick Actions</h2>

            <div class="quick-actions-grid">
                <RouterLink
                    v-for="action in quickActions"
                    :key="action.label"
                    :to="action.to"
                    class="quick-action"
                >
                    {{ action.label }}
                </RouterLink>
            </div>
        </section>

        <!-- Statistics Section For Active Bookings, Registered Coaches, and Players -->
        <section class="stats-section">
            <h2 class="section-title">Quick Stats</h2>

            <div class="stats-grid">
                <article
                    v-for="stat in stats"
                    :key="stat.label"
                    class="stat-block"
                >
                    <p class="stat-label">{{ stat.label }}</p>
                    <h3 class="stat-value">{{ stat.value }}</h3>
                </article>
            </div>
        </section>
    </main>
</template>

<style scoped>
.admin-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-top: 1.5rem;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
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
}

.page-message {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.95rem;
    font-weight: 600;
}

.page-message.error {
    color: var(--ps-danger);
}

.quick-actions-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stats-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.section-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ps-text);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, max-content));
    gap: 1rem;
    justify-content: start;
}

.quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    width: min(100%, 260px);
    padding: 1rem;
    border-radius: 18px;
    background: var(--ps-brand);
    color: #ffffff;
    text-align: center;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(108, 62, 148, 0.18);
    transition: background 0.15s ease, transform 0.15s ease;
}

.quick-action:hover {
    background: var(--ps-brand-dark);
    transform: translateY(-1px);
}

.quick-action:focus-visible {
    outline: 3px solid rgba(255, 255, 255, 0.9);
    outline-offset: 3px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.stat-block {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 150px;
    border-radius: 20px;
    border: 1px solid var(--ps-border-soft);
    background: #ffffff;
    padding: 1.25rem;
    box-shadow: 0 1px 2px rgba(24, 18, 33, 0.04);
}

.stat-label {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.stat-value {
    margin: 0.5rem 0;
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    line-height: 1.1;
    color: var(--ps-text);
}

.stat-detail {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.9rem;
}

@media (max-width: 640px) {
    .admin-dashboard {
        gap: 1rem;
        padding-top: 1rem;
    }

    .page-title {
        font-size: clamp(1.8rem, 8vw, 2.2rem);
    }

    .quick-actions-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .quick-action {
        min-height: 88px;
    }
}
</style>
