<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import LoadingSpinner from '../components/LoadingSpinner.vue';
import { http } from '../services/http.js';

const quickActions = ref([
    { label: 'Manage users', to: { name: 'admin-users' } },
    { label: 'Manage courts', to: { name: 'admin-courts' } },
    { label: 'Registration payments', to: { name: 'admin-registration-payments' } },
]);

const defaultStats = [
    { label: 'Active bookings', value: 0, detail: '' },
    { label: 'Coaches registered', value: 0, detail: '' },
    { label: 'Players registered', value: 0, detail: '' },
];

const stats = ref(defaultStats.map((stat) => ({ ...stat })));
const statsLoading = ref(false);

const utilisationFrom = ref('');
const utilisationTo = ref('');
const utilisationSeries = ref([]);
const utilisationLoading = ref(false);
const utilisationError = ref('');
const activePointIndex = ref(null);

const courts = ref([]);
const selectedCourtIds = ref([]);
const courtDropdownOpen = ref(false);
const courtDropdownRef = ref(null);

const dashboardLoading = computed(() => statsLoading.value || utilisationLoading.value);

const courtSelectionLabel = computed(() => {
    const total = courts.value.filter((c) => (c?.status ?? '') === 'ACTIVE').length;
    const selected = selectedCourtIds.value.length;
    if (selected === 0) return 'No courts selected';
    if (selected === total) return 'All courts';
    return `${selected} of ${total} courts`;
});

function toggleCourtSelection(courtId) {
    const idx = selectedCourtIds.value.indexOf(courtId);
    if (idx === -1) {
        selectedCourtIds.value = [...selectedCourtIds.value, courtId];
    } else {
        selectedCourtIds.value = selectedCourtIds.value.filter((id) => id !== courtId);
    }
}

function selectAllCourts() {
    selectedCourtIds.value = courts.value
        .filter((c) => (c?.status ?? '') === 'ACTIVE')
        .map((c) => c.id);
}

function deselectAllCourts() {
    selectedCourtIds.value = [];
}

function handleClickOutside(event) {
    if (courtDropdownRef.value && !courtDropdownRef.value.contains(event.target)) {
        courtDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

function normalizeStat(stat, index) {
    return {
        label: stat.label || stat.title || stat.name || `Stat ${index + 1}`,
        value: stat.value ?? stat.count ?? stat.total ?? '-',
        detail: stat.detail || stat.subtitle || stat.summary || '',
    };
}

function formatShortDate(iso) {
    if (!iso) return '';
    const dt = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(dt.getTime())) return String(iso);
    return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function formatLongDate(iso) {
    if (!iso) return '';
    const dt = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(dt.getTime())) return String(iso);
    return dt.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadStats() {
    statsLoading.value = true;

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
    } finally {
        statsLoading.value = false;
    }
}

function dateInputValue(date) {
    return new Date(date.getTime() - date.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
}

async function loadUtilisation() {
    if (!utilisationFrom.value || !utilisationTo.value) return;

    utilisationLoading.value = true;
    utilisationError.value = '';

    try {
        const courtQuery = selectedCourtIds.value.length
            ? `&courts=${encodeURIComponent(selectedCourtIds.value.join(','))}`
            : '';

        const response = await http.get(
            `/dashboard/utilisation?from=${encodeURIComponent(utilisationFrom.value)}&to=${encodeURIComponent(utilisationTo.value)}${courtQuery}`
        );
        utilisationSeries.value = Array.isArray(response?.series) ? response.series : [];
    } catch (error) {
        console.error('Failed to load utilisation:', error);
        utilisationError.value = error?.message ?? 'Unable to load utilisation right now.';
        utilisationSeries.value = [];
    } finally {
        utilisationLoading.value = false;
    }
}

function smoothPath(points) {
    if (points.length < 2) {
        return points.length === 1 ? `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}` : '';
    }
    if (points.length === 2) {
        return `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)} L ${points[1].x.toFixed(2)} ${points[1].y.toFixed(2)}`;
    }

    let d = `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`;

    for (let i = 0; i < points.length - 1; i++) {
        const p0 = points[Math.max(i - 1, 0)];
        const p1 = points[i];
        const p2 = points[i + 1];
        const p3 = points[Math.min(i + 2, points.length - 1)];

        const tension = 0.3;
        const cp1x = p1.x + (p2.x - p0.x) * tension;
        const cp1y = p1.y + (p2.y - p0.y) * tension;
        const cp2x = p2.x - (p3.x - p1.x) * tension;
        const cp2y = p2.y - (p3.y - p1.y) * tension;

        d += ` C ${cp1x.toFixed(2)} ${cp1y.toFixed(2)}, ${cp2x.toFixed(2)} ${cp2y.toFixed(2)}, ${p2.x.toFixed(2)} ${p2.y.toFixed(2)}`;
    }

    return d;
}

const chart = computed(() => {
    const width = 640;
    const height = 240;
    const padLeft = 46;
    const padRight = 18;
    const padTop = 16;
    const padBottom = 34;

    const series = utilisationSeries.value ?? [];
    if (!Array.isArray(series) || series.length === 0) {
        return { width, height, d: '', areaD: '', points: [], yTicks: [], xTicks: [] };
    }

    const innerW = width - padLeft - padRight;
    const innerH = height - padTop - padBottom;
    const yMin = 0;
    const yMax = 1;

    const points = series.map((p, i) => {
        const value = Math.max(0, Math.min(1, Number(p?.utilisation ?? 0)));
        const x = padLeft + (innerW * (series.length === 1 ? 0.5 : i / (series.length - 1)));
        const norm = (value - yMin) / (yMax - yMin || 1);
        const y = padTop + innerH * (1 - norm);
        return {
            x,
            y,
            date: p?.date,
            utilisation: value,
            bookedCells: Number(p?.bookedCells ?? 0),
            capacityCells: Number(p?.capacityCells ?? 0),
        };
    });

    const d = smoothPath(points);

    const baseY = padTop + innerH;
    const areaD = points.length
        ? `${d} L ${points[points.length - 1].x.toFixed(2)} ${baseY.toFixed(2)} L ${points[0].x.toFixed(2)} ${baseY.toFixed(2)} Z`
        : '';

    const yTicks = [0, 0.25, 0.5, 0.75, 1].map((value) => ({
        value,
        y: padTop + innerH * (1 - value),
    }));

    const indices = (() => {
        if (points.length <= 7) {
            return points.map((_, i) => i);
        }

        const last = points.length - 1;
        const q1 = Math.round(last * 0.25);
        const q2 = Math.round(last * 0.5);
        const q3 = Math.round(last * 0.75);

        return Array.from(new Set([0, q1, q2, q3, last]));
    })();

    const xTicks = indices.map((i) => ({
        index: i,
        x: points[i]?.x ?? padLeft,
        label: formatShortDate(points[i]?.date),
    }));

    return { width, height, d, areaD, points, yTicks, xTicks, padLeft, padRight, padTop, padBottom };
});

const activePoint = computed(() => {
    const idx = activePointIndex.value;
    if (idx === null || idx === undefined) return null;
    return chart.value.points?.[idx] ?? null;
});

const avgUtilisation = computed(() => {
    const series = utilisationSeries.value ?? [];
    if (series.length === 0) return 0;
    const sum = series.reduce((acc, p) => acc + Number(p?.utilisation ?? 0), 0);
    return Math.round((sum / series.length) * 100);
});

const peakUtilisation = computed(() => {
    const series = utilisationSeries.value ?? [];
    if (series.length === 0) return 0;
    const max = Math.max(...series.map((p) => Number(p?.utilisation ?? 0)));
    return Math.round(max * 100);
});

const latestUtilisation = computed(() => {
    const last = utilisationSeries.value?.[utilisationSeries.value.length - 1];
    const value = Number(last?.utilisation ?? 0);
    return Number.isFinite(value) ? Math.round(value * 100) : 0;
});

onMounted(() => {
    const today = new Date();
    const from = new Date();
    from.setDate(today.getDate() - 13);

    utilisationFrom.value = dateInputValue(from);
    utilisationTo.value = dateInputValue(today);

    loadStats();
    http.get('/admin/courts')
        .then((response) => {
            const payload = Array.isArray(response) ? response : [];
            courts.value = payload;
            selectedCourtIds.value = payload
                .filter((c) => (c?.status ?? '') === 'ACTIVE')
                .map((c) => c.id);
        })
        .catch(() => {
            courts.value = [];
            selectedCourtIds.value = [];
        })
        .finally(() => {
            loadUtilisation();
        });
});

watch([utilisationFrom, utilisationTo, selectedCourtIds], () => {
    loadUtilisation();
});

watch(utilisationSeries, () => {
    activePointIndex.value = null;
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

        <div v-if="dashboardLoading" class="dashboard-loader" aria-busy="true" aria-live="polite">
            <LoadingSpinner label="Loading dashboard…" />
        </div>

        <template v-else>

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

        <section class="utilisation-section">
            <div class="utilisation-head">
                <div>
                    <h2 class="section-title">Court utilisation</h2>
                    <p class="utilisation-sub">Time-slot based &middot; <strong>{{ latestUtilisation }}%</strong> latest</p>
                </div>

                <div class="utilisation-controls">
                    <div class="utilisation-field court-dropdown" ref="courtDropdownRef">
                        <span class="utilisation-label">Courts</span>
                        <button
                            type="button"
                            class="court-dropdown__trigger"
                            :class="{ 'court-dropdown__trigger--open': courtDropdownOpen }"
                            @click="courtDropdownOpen = !courtDropdownOpen"
                        >
                            <span class="court-dropdown__text">{{ courtSelectionLabel }}</span>
                            <span class="court-dropdown__caret">&#9662;</span>
                        </button>

                        <div v-if="courtDropdownOpen" class="court-dropdown__panel">
                            <div class="court-dropdown__actions">
                                <button type="button" class="court-dropdown__action" @click="selectAllCourts">Select all</button>
                                <button type="button" class="court-dropdown__action" @click="deselectAllCourts">Clear</button>
                            </div>
                            <div class="court-dropdown__list">
                                <label
                                    v-for="court in courts"
                                    :key="court.id"
                                    class="court-dropdown__item"
                                    :class="{ 'court-dropdown__item--disabled': (court.status ?? '') !== 'ACTIVE' }"
                                >
                                    <input
                                        type="checkbox"
                                        class="court-dropdown__check"
                                        :checked="selectedCourtIds.includes(court.id)"
                                        :disabled="(court.status ?? '') !== 'ACTIVE'"
                                        @change="toggleCourtSelection(court.id)"
                                    />
                                    <span class="court-dropdown__name">
                                        {{ court.name }}
                                        <span v-if="(court.status ?? '') !== 'ACTIVE'" class="court-dropdown__inactive">(inactive)</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <label class="utilisation-field">
                        <span class="utilisation-label">From</span>
                        <input v-model="utilisationFrom" type="date" />
                    </label>
                    <label class="utilisation-field">
                        <span class="utilisation-label">To</span>
                        <input v-model="utilisationTo" type="date" />
                    </label>
                </div>
            </div>

            <div class="utilisation-card">
                <p v-if="utilisationLoading" class="utilisation-muted">Loading utilisation…</p>
                <p v-else-if="utilisationError" class="utilisation-muted utilisation-error">{{ utilisationError }}</p>
                <p v-else-if="utilisationSeries.length === 0" class="utilisation-muted">No data available.</p>

                <template v-else>
                    <div class="utilisation-summary">
                        <div class="utilisation-summary__item">
                            <span class="utilisation-summary__value">{{ avgUtilisation }}%</span>
                            <span class="utilisation-summary__label">Avg</span>
                        </div>
                        <div class="utilisation-summary__item">
                            <span class="utilisation-summary__value">{{ peakUtilisation }}%</span>
                            <span class="utilisation-summary__label">Peak</span>
                        </div>
                        <div class="utilisation-summary__item">
                            <span class="utilisation-summary__value">{{ latestUtilisation }}%</span>
                            <span class="utilisation-summary__label">Latest</span>
                        </div>
                        <div class="utilisation-summary__item">
                            <span class="utilisation-summary__value">{{ selectedCourtIds.length }}</span>
                            <span class="utilisation-summary__label">Courts</span>
                        </div>
                    </div>

                    <svg
                        class="utilisation-chart"
                        :width="chart.width"
                        :height="chart.height"
                        :viewBox="`0 0 ${chart.width} ${chart.height}`"
                        role="img"
                        aria-label="Court utilisation line chart"
                        @mouseleave="activePointIndex = null"
                    >
                        <defs>
                            <linearGradient id="utilLine" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="#8b67ab" />
                                <stop offset="50%" stop-color="#6c3e94" />
                                <stop offset="100%" stop-color="#56306f" />
                            </linearGradient>

                            <linearGradient id="utilArea" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6c3e94" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#6c3e94" stop-opacity="0.02" />
                            </linearGradient>
                        </defs>

                        <g class="utilisation-chart__axes" aria-hidden="true">
                            <path
                                v-for="tick in chart.yTicks"
                                :key="tick.value"
                                class="utilisation-chart__grid"
                                :d="`M ${chart.padLeft} ${tick.y} L ${chart.width - chart.padRight} ${tick.y}`"
                            />

                            <text
                                v-for="tick in chart.yTicks"
                                :key="`label-${tick.value}`"
                                class="utilisation-chart__axis-label"
                                :x="chart.padLeft - 10"
                                :y="tick.y + 4"
                                text-anchor="end"
                            >
                                {{ Math.round(tick.value * 100) }}%
                            </text>

                            <text
                                v-for="tick in chart.xTicks"
                                :key="`x-${tick.index}`"
                                class="utilisation-chart__axis-label"
                                :x="tick.x"
                                :y="chart.height - 10"
                                text-anchor="middle"
                            >
                                {{ tick.label }}
                            </text>
                        </g>

                        <path
                            class="utilisation-chart__area"
                            :d="chart.areaD"
                            fill="url(#utilArea)"
                        />

                        <line
                            v-if="activePoint"
                            class="utilisation-chart__crosshair"
                            :x1="activePoint.x"
                            :x2="activePoint.x"
                            :y1="chart.padTop"
                            :y2="chart.height - chart.padBottom"
                            aria-hidden="true"
                        />

                        <path
                            class="utilisation-chart__line"
                            :d="chart.d"
                            fill="none"
                            stroke="url(#utilLine)"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <g class="utilisation-chart__points">
                            <circle
                                v-for="(p, idx) in chart.points"
                                :key="p.date || idx"
                                class="utilisation-chart__dot"
                                :class="{ 'utilisation-chart__dot--active': idx === activePointIndex }"
                                :cx="p.x"
                                :cy="p.y"
                                :r="idx === activePointIndex ? 5.5 : 3.5"
                                tabindex="0"
                                role="img"
                                :aria-label="`${formatLongDate(p.date)}: ${Math.round(p.utilisation * 100)}% utilisation (${p.bookedCells} of ${p.capacityCells} time-slots booked)`"
                                @mouseenter="activePointIndex = idx"
                                @mouseleave="activePointIndex = null"
                                @focus="activePointIndex = idx"
                                @blur="activePointIndex = null"
                            />
                        </g>

                        <g v-if="activePoint" class="utilisation-chart__tooltip-group">
                            <rect
                                :x="Math.min(Math.max(activePoint.x - 70, 4), chart.width - 144)"
                                :y="Math.max(activePoint.y - 52, 2)"
                                width="140"
                                height="44"
                                rx="8"
                                fill="rgba(28, 25, 34, 0.92)"
                            />
                            <text
                                :x="Math.min(Math.max(activePoint.x, 74), chart.width - 74)"
                                :y="Math.max(activePoint.y - 33, 17)"
                                text-anchor="middle"
                                fill="#fff"
                                font-size="11"
                                font-weight="700"
                            >
                                {{ formatShortDate(activePoint.date) }} &middot; {{ Math.round(activePoint.utilisation * 100) }}%
                            </text>
                            <text
                                :x="Math.min(Math.max(activePoint.x, 74), chart.width - 74)"
                                :y="Math.max(activePoint.y - 16, 34)"
                                text-anchor="middle"
                                fill="rgba(255,255,255,0.7)"
                                font-size="10"
                                font-weight="600"
                            >
                                {{ activePoint.bookedCells }}/{{ activePoint.capacityCells }} time-slots booked
                            </text>
                        </g>
                    </svg>

                    <div class="utilisation-detail">
                        <p v-if="!activePoint" class="utilisation-muted utilisation-detail__hint">
                            Hover a point to see daily breakdown by time-slot.
                        </p>
                        <div v-else class="utilisation-detail__grid">
                            <div class="utilisation-detail__item">
                                <span class="utilisation-detail__label">Date</span>
                                <span class="utilisation-detail__value">{{ formatLongDate(activePoint.date) }}</span>
                            </div>
                            <div class="utilisation-detail__item">
                                <span class="utilisation-detail__label">Utilisation</span>
                                <span class="utilisation-detail__value">{{ Math.round(activePoint.utilisation * 100) }}%</span>
                            </div>
                            <div class="utilisation-detail__item">
                                <span class="utilisation-detail__label">Time-slots booked</span>
                                <span class="utilisation-detail__value">{{ activePoint.bookedCells }}</span>
                            </div>
                            <div class="utilisation-detail__item">
                                <span class="utilisation-detail__label">Total capacity</span>
                                <span class="utilisation-detail__value">{{ activePoint.capacityCells }} slots</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        </template>
    </main>
</template>

<style scoped>
.admin-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
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
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ps-brand);
}

.page-title {
    margin: 0.2rem 0 0;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 800;
    letter-spacing: -0.02em;
}

.page-message {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.9rem;
    font-weight: 600;
}

.page-message.error {
    color: var(--ps-danger);
}

.quick-actions-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.stats-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.dashboard-loader {
    border-radius: var(--ps-radius-lg, 16px);
    border: 1px solid var(--ps-border-soft);
    background: var(--ps-surface);
    box-shadow: var(--ps-shadow-sm);
    padding: 2.25rem 1.25rem;
    display: grid;
    place-items: center;
}

.utilisation-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.utilisation-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.utilisation-sub {
    margin: 0.25rem 0 0;
    color: var(--ps-text-muted);
    font-size: 0.85rem;
}

.utilisation-controls {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.utilisation-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.utilisation-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--ps-text-faint);
}

/* Custom court multi-select dropdown */
.court-dropdown {
    position: relative;
    min-width: 200px;
}

.court-dropdown__trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    width: 100%;
    padding: 0.6rem 0.75rem;
    font: inherit;
    font-size: 0.9rem;
    color: var(--ps-text);
    background: var(--ps-surface);
    border: 1.5px solid #d4c9e2;
    border-radius: var(--ps-radius-sm);
    min-height: 42px;
    cursor: pointer;
    transition: border-color var(--ps-transition), box-shadow var(--ps-transition);
    text-align: left;
}

.court-dropdown__trigger:hover {
    border-color: var(--ps-brand-light);
}

.court-dropdown__trigger--open {
    border-color: var(--ps-brand);
    box-shadow: 0 0 0 3px var(--ps-brand-glow);
}

.court-dropdown__text {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    font-size: 0.85rem;
}

.court-dropdown__caret {
    font-size: 0.7rem;
    color: var(--ps-text-faint);
    transition: transform 0.2s ease;
}

.court-dropdown__trigger--open .court-dropdown__caret {
    transform: rotate(180deg);
}

.court-dropdown__panel {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    min-width: 220px;
    z-index: 40;
    background: var(--ps-surface-elevated, #fff);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-sm);
    box-shadow: var(--ps-shadow-lg);
    animation: dropdown-in 0.15s ease;
    overflow: hidden;
}

.court-dropdown__actions {
    display: flex;
    gap: 0.25rem;
    padding: 0.5rem 0.65rem;
    border-bottom: 1px solid var(--ps-border-soft);
    background: rgba(242, 236, 247, 0.3);
}

.court-dropdown__action {
    padding: 0.2rem 0.5rem;
    font: inherit;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ps-brand);
    background: none;
    border: 1px solid transparent;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.court-dropdown__action:hover {
    background: var(--ps-brand-tint);
    border-color: #ddcfe9;
}

.court-dropdown__list {
    max-height: 200px;
    overflow-y: auto;
    padding: 0.35rem 0;
}

.court-dropdown__item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.75rem;
    cursor: pointer;
    transition: background 0.12s ease;
    font-size: 0.85rem;
}

.court-dropdown__item:hover {
    background: rgba(108, 62, 148, 0.04);
}

.court-dropdown__item--disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.court-dropdown__check {
    width: 16px;
    height: 16px;
    accent-color: var(--ps-brand);
    cursor: inherit;
    flex-shrink: 0;
}

.court-dropdown__name {
    font-weight: 600;
    color: var(--ps-text);
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.court-dropdown__inactive {
    font-weight: 500;
    color: var(--ps-text-faint);
    font-size: 0.78rem;
}

@keyframes dropdown-in {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.utilisation-card {
    border-radius: var(--ps-radius-lg, 16px);
    border: 1px solid var(--ps-border-soft);
    background: var(--ps-surface);
    padding: 1.25rem;
    box-shadow: var(--ps-shadow-sm);
}

.utilisation-summary {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.utilisation-summary__item {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
    padding: 0.4rem 0.75rem;
    border-radius: var(--ps-radius-sm);
    background: linear-gradient(135deg, rgba(108, 62, 148, 0.07) 0%, rgba(108, 62, 148, 0.02) 100%);
    border: 1px solid rgba(221, 207, 233, 0.5);
}

.utilisation-summary__value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ps-brand);
}

.utilisation-summary__label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--ps-text-faint);
}

.utilisation-muted {
    margin: 0;
    color: var(--ps-text-muted);
    font-weight: 600;
    font-size: 0.9rem;
}

.utilisation-error {
    color: var(--ps-danger);
}

.utilisation-chart {
    width: 100%;
    height: auto;
    display: block;
    margin-top: 0.5rem;
}

.utilisation-chart__grid {
    stroke: rgba(108, 62, 148, 0.08);
    stroke-width: 1;
    stroke-dasharray: 3 3;
}

.utilisation-chart__axis-label {
    fill: var(--ps-text-faint);
    font-size: 10px;
    font-weight: 650;
}

.utilisation-chart__area {
    pointer-events: none;
}

.utilisation-chart__crosshair {
    stroke: rgba(108, 62, 148, 0.3);
    stroke-width: 1;
    stroke-dasharray: 4 4;
}

.utilisation-chart__line {
    filter: drop-shadow(0 4px 8px rgba(108, 62, 148, 0.15));
}

.utilisation-chart__dot {
    fill: #ffffff;
    stroke: #6c3e94;
    stroke-width: 2;
    cursor: pointer;
    transition: all 0.15s ease;
    transform-origin: center;
}

.utilisation-chart__dot:hover,
.utilisation-chart__dot--active {
    fill: #6c3e94;
    stroke: #ffffff;
    filter: drop-shadow(0 2px 6px rgba(108, 62, 148, 0.4));
}

.utilisation-chart__dot:focus-visible {
    outline: none;
    filter: drop-shadow(0 4px 10px rgba(108, 62, 148, 0.35));
}

.utilisation-chart__tooltip-group text {
    pointer-events: none;
}

.utilisation-chart__tooltip-group rect {
    pointer-events: none;
}

.utilisation-detail {
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid var(--ps-border-soft);
}

.utilisation-detail__hint {
    margin: 0;
}

.utilisation-detail__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.6rem;
}

.utilisation-detail__item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.6rem 0.7rem;
    border-radius: var(--ps-radius-sm);
    background: linear-gradient(135deg, rgba(108, 62, 148, 0.06) 0%, rgba(108, 62, 148, 0.02) 100%);
    border: 1px solid rgba(221, 207, 233, 0.5);
}

.utilisation-detail__label {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--ps-text-faint);
}

.utilisation-detail__value {
    font-size: 0.88rem;
    font-weight: 750;
    color: var(--ps-text);
}

.section-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--ps-text);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, max-content));
    gap: 1rem;
    justify-content: start;
}

.quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 110px;
    width: min(100%, 260px);
    padding: 1.25rem;
    border-radius: var(--ps-radius-lg, 16px);
    background: linear-gradient(135deg, #7b4fa6 0%, #6c3d91 50%, #56306f 100%);
    color: #ffffff;
    text-align: center;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 700;
    box-shadow: 0 6px 20px rgba(108, 62, 148, 0.25);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.quick-action::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 60%);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.quick-action:hover::after {
    opacity: 1;
}

.quick-action:hover {
    box-shadow: 0 10px 28px rgba(108, 62, 148, 0.35);
    transform: translateY(-3px);
}

.quick-action:active {
    transform: translateY(0) scale(0.98);
}

.quick-action:focus-visible {
    outline: 3px solid rgba(255, 255, 255, 0.9);
    outline-offset: 3px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-block {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px;
    border-radius: var(--ps-radius-lg, 16px);
    border: 1px solid var(--ps-border-soft);
    background: var(--ps-surface);
    padding: 1.25rem;
    box-shadow: var(--ps-shadow-sm);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-block:hover {
    box-shadow: var(--ps-shadow);
    transform: translateY(-2px);
    border-color: rgba(108, 62, 148, 0.15);
}

.stat-label {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.stat-value {
    margin: 0.5rem 0;
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    font-weight: 800;
    line-height: 1.1;
    color: var(--ps-text);
}

.stat-detail {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.85rem;
}

@media (max-width: 640px) {
    .admin-dashboard {
        gap: 1.25rem;
        padding-top: 1rem;
    }

    .page-title {
        font-size: clamp(1.6rem, 8vw, 2rem);
    }

    .quick-actions-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .quick-action {
        min-height: 80px;
    }

    .utilisation-summary {
        gap: 0.5rem;
    }

    .utilisation-summary__item {
        flex: 1;
        min-width: 0;
        justify-content: center;
    }

    .court-dropdown {
        min-width: 100%;
    }
}
</style>
