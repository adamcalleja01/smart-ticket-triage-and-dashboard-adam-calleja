<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BeMoLayout from '@/Layouts/BeMoLayout.vue'
import { Head } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref, nextTick } from 'vue'
import { Chart, BarElement, BarController, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'
import { Link } from '@inertiajs/vue3'

Chart.register(BarElement, BarController, CategoryScale, LinearScale, Tooltip, Legend)

const stats = ref([]);

const categoryCanvas = ref(null)
const statusCanvas = ref(null)
const dashboardRoot = ref(null)
let categoryChart = null
let statusChart = null

const createBarChart = (ctx, labels, data, labelText, color) => {
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: labelText,
          data,
          backgroundColor: color,
          borderColor: color,
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true },
      },
    },
  })
}

const buildCharts = async () => {
  await nextTick()
  // categories
  const categories = stats.value?.category ?? {}
  const catLabels = Object.keys(categories)
  const catData = catLabels.map(k => categories[k] ?? 0)

  // statuses
  const statuses = stats.value?.status ?? {}
  const statusLabels = Object.keys(statuses)
  const statusData = statusLabels.map(k => statuses[k] ?? 0)

  // destroy previous instances if present
  if (categoryChart) { categoryChart.destroy(); categoryChart = null }
  if (statusChart) { statusChart.destroy(); statusChart = null }

  if (categoryCanvas.value) {
    const ctx = categoryCanvas.value.getContext('2d')
    categoryChart = createBarChart(ctx, catLabels, catData, 'Tickets by Category', 'rgba(54, 162, 235, 0.7)')
  }

  if (statusCanvas.value) {
    const ctx2 = statusCanvas.value.getContext('2d')
    statusChart = createBarChart(ctx2, statusLabels, statusData, 'Tickets by Status', 'rgba(255, 0, 0, 1)')
  }
}

const toggleTheme = () => {
  // kept for backward compatibility if other components call it; actual theme toggle lives in layout
}

/**
 * Fetch statistics from the API endpoint.
 */
const fetchStats = async () => {
  try {
    const response = await fetch(route('api.stats'));
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    stats.value = await response.json();
    console.log(stats.value);
    buildCharts();
  } catch (error) {
    console.error('There was a problem with the fetch operation:', error);
  }
}

onMounted(() => {
  fetchStats();
})

onUnmounted(() => {
  if (categoryChart) { categoryChart.destroy(); categoryChart = null }
  if (statusChart) { statusChart.destroy(); statusChart = null }
})
</script>

<template>

  <Head title="Dashboard" />

  <BeMoLayout>
    <section ref="dashboardRoot" class="dashboard">
      <header class="dashboard__header">
        <div class="dashboard__header-row">
          <h1 class="dashboard__title">Overview</h1>
          <div class="dashboard__theme">
            <Link :href="route('tickets.index')" class="dashboard__theme-toggle">
            View All Tickets
            </Link>

          </div>
        </div>
        <p class="dashboard__summary"><span class="dashboard__total">{{ stats?.total_tickets ?? 0 }}</span> tickets in
          total.</p>
      </header>

      <div class="dashboard__charts">
        <article class="dashboard__chart-card dashboard__chart-card--category">
          <h3 class="dashboard__chart-title">Categories</h3>
          <div class="dashboard__chart-body">
            <canvas ref="categoryCanvas" class="dashboard__canvas" width="400" height="200"></canvas>
          </div>
        </article>

        <article class="dashboard__chart-card dashboard__chart-card--status">
          <h3 class="dashboard__chart-title">Statuses</h3>
          <div class="dashboard__chart-body">
            <canvas ref="statusCanvas" class="dashboard__canvas" width="400" height="200"></canvas>
          </div>
        </article>
      </div>
    </section>
  </BeMoLayout>
</template>

<style>
/* Base theme tokens */
:root {
  --dash-bg: #ffffff;
  --dash-surface: #f9fafb;
  --dash-text: #0f172a;
  --dash-muted: #64748b;
  --dash-border: #e5e7eb;
  --dash-accent: #2563eb;
  --dash-accent-2: #16a34a;
  --dash-radius: 14px;
  --dash-shadow: 0 8px 24px rgba(2, 6, 23, 0.06);
  --dash-space-1: 8px;
  --dash-space-2: 12px;
  --dash-space-3: 16px;
  --dash-space-4: 24px;
  --dash-space-5: 32px;
  --dash-maxw: 1200px;
}

/* Explicit attribute-based overrides take priority over prefers-color-scheme */
:root[data-bemo-theme="dark"] {
  --dash-bg: #0b1020;
  --dash-surface: #0f172a;
  --dash-text: #e5e7eb;
  --dash-muted: #9aa4b2;
  --dash-border: #1f2937;
  --dash-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

:root[data-bemo-theme="light"] {
  --dash-bg: #ffffff;
  --dash-surface: #f9fafb;
  --dash-text: #0f172a;
  --dash-muted: #64748b;
  --dash-border: #e5e7eb;
  --dash-shadow: 0 8px 24px rgba(2, 6, 23, 0.06);
}

@media (prefers-color-scheme: dark) {
  :root {
    --dash-bg: #0b1020;
    --dash-surface: #0f172a;
    --dash-text: #e5e7eb;
    --dash-muted: #9aa4b2;
    --dash-border: #1f2937;
    --dash-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
  }
}

/* Explicit dark modifier for JS toggle (BEM modifier) */
.dashboard--dark {
  --dash-bg: #0b1020;
  --dash-surface: #0f172a;
  --dash-text: #e5e7eb;
  --dash-muted: #9aa4b2;
  --dash-border: #1f2937;
  --dash-accent: #60a5fa;
  --dash-accent-2: #34d399;
  --dash-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

/* Dashboard block */
.dashboard {
  box-sizing: border-box;
  max-width: var(--dash-maxw);
  margin: 0 auto;
  padding: var(--dash-space-5) var(--dash-space-4);
  background: var(--dash-bg);
  color: var(--dash-text);
}

/* Header element */
.dashboard__header {
  display: grid;
  gap: var(--dash-space-2);
  margin-bottom: var(--dash-space-5);
}

.dashboard__header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--dash-space-3);
}

.dashboard__title {
  font-size: clamp(22px, 2.4vw, 28px);
  line-height: 1.2;
  margin: 0;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.dashboard__summary {
  margin: 0;
  color: var(--dash-muted);
  font-size: 14px;
}

.dashboard__total {
  color: var(--dash-text);
  font-weight: 700;
}

/* Charts layout */
.dashboard__charts {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: var(--dash-space-4);
}

@media (max-width: 920px) {
  .dashboard__charts {
    grid-template-columns: 1fr;
  }
}

/* Chart card */
.dashboard__chart-card {
  grid-column: span 6;
  background: var(--dash-surface);
  border: 1px solid var(--dash-border);
  border-radius: var(--dash-radius);
  box-shadow: var(--dash-shadow);
  overflow: clip;
  transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
}

.dashboard__chart-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(2, 6, 23, 0.08);
  border-color: color-mix(in srgb, var(--dash-border) 50%, var(--dash-accent));
}

/* Modifiers for colored top borders */
.dashboard__chart-card--category {
  border-top: 3px solid var(--dash-accent);
}

.dashboard__chart-card--status {
  border-top: 3px solid var(--dash-accent-2);
}

/* Title & body */
.dashboard__chart-title {
  margin: 0;
  padding: var(--dash-space-3) var(--dash-space-4) 0 var(--dash-space-4);
  font-size: 16px;
  font-weight: 600;
  color: var(--dash-text);
}

.dashboard__chart-body {
  padding: var(--dash-space-4);
}

/* Canvas should scale responsively */
.dashboard__canvas {
  display: block;
  width: 100%;
  height: auto;
  /* lets Chart.js manage aspect ratio if set */
  max-height: 320px;
  /* safety cap; adjust as needed */
  outline: none;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  .dashboard__chart-card {
    transition: none;
  }

  .dashboard__chart-card:hover {
    transform: none;
  }
}
</style>
