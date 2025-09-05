<script setup lang="js">
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'

// Theme state: 'dark' | 'light' | '' (follow system)
const theme = ref('')

/**
 * Applies the theme class to the document.
 * @param mode - 'dark' | 'light' | '' (follow system)
 */
const applyThemeClass = (mode) => {
    try {
        const doc = document.documentElement
        if (mode === 'dark') {
            doc.setAttribute('data-bemo-theme', 'dark')
            doc.classList.add('dashboard--dark')
        } else if (mode === 'light') {
            doc.setAttribute('data-bemo-theme', 'light')
            doc.classList.remove('dashboard--dark')
        } else {
            doc.removeAttribute('data-bemo-theme')
            doc.classList.remove('dashboard--dark')
        }
    } catch (e) {
        // ignore
    }
}

/**
 * Sets the theme for the application.
 * @param mode - 'dark' | 'light' | '' (follow system)
 */
const setTheme = (mode) => {
    theme.value = mode
    try {
        if (mode === 'dark' || mode === 'light') {
            localStorage.setItem('bemo-dashboard-theme', mode)
        } else {
            localStorage.removeItem('bemo-dashboard-theme')
        }
    } catch (e) {
        // ignore storage errors
    }
    applyThemeClass(mode)
}

/**
 * Toggles between dark and light themes.
 */
const toggleTheme = () => {
    const next = theme.value === 'dark' ? 'light' : 'dark'
    setTheme(next)
}

onMounted(() => {
    try {
        const stored = localStorage.getItem('bemo-dashboard-theme')
        if (stored === 'dark' || stored === 'light') {
            setTheme(stored)
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark')
        } else {
            setTheme('light')
        }
    } catch (e) {
        // fallback to system or light
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark')
        } else {
            setTheme('light')
        }
    }
})
</script>

<template>
    <div class="layout">
        <header class="layout__header">
            <Link :href="route('dashboard')" aria-label="BeMo Dashboard Home">
            <div class="layout__brand">
                <img class="layout__brand-logo"
                    src="https://assets.bemoacademicconsulting.com/files/AR8wdupQbJbHsczmjaMMjGId3cNRjmPLolORynZB.png"
                    alt="BeMo Logo" />
                <h1 class="layout__brand-title">Ticket Triage System</h1>
            </div>

            <div class="layout__header-controls">
                <button type="button" class="layout__theme-toggle" :aria-pressed="theme === 'dark'"
                    @click="toggleTheme">
                    <span class="layout__theme-icon" aria-hidden="true">{{ theme === 'dark' ? '☀️' : '🌙' }}</span>
                    <span class="layout__theme-label">{{ theme === 'dark' ? 'Light' : 'Dark' }}</span>
                </button>
            </div>
            </Link>

        </header>

        <main class="layout__content">
            <slot />
        </main>

        <footer class="layout__footer">
            <p class="layout__footer-text">© 2025 BeMo</p>
        </footer>
    </div>
</template>

<style>
.layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: var(--dash-bg, #f9fafb);
    color: var(--dash-text, #1f2937);
    position: relative;
}

.layout__header {
    background: var(--dash-accent, #5c7fca);
    color: #fff;
    padding: 1rem;
}


.layout__header {
    background: var(--dash-accent, #5c7fca);
    color: #fff;
    padding: 1.5rem 2rem;
    box-shadow: 0 2px 8px rgba(60, 80, 130, 0.08);
}

.layout__brand {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.layout__brand-logo {
    height: 48px;
    width: 48px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(60, 80, 130, 0.10);
    background: #fff;
    object-fit: contain;
}

.layout__brand-title {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #fff;
    font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
}

.layout__footer {
    background: var(--dash-surface, #e9eef6);
    padding: 1.25rem 1.5rem;
    text-align: center;
    margin-top: auto;
    width: 100%;
    box-shadow: 0 -6px 18px rgba(16, 24, 40, 0.06);
}

.layout__footer-text {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--dash-text, #0f172a);
    letter-spacing: 0.01em;
}

/* Safety: when theme class is applied to document root, ensure layout header/footer adjust */
.dashboard--dark .layout {
    background: var(--dash-bg);
    color: var(--dash-text);
}

.dashboard--dark .layout__header {
    background: color-mix(in srgb, var(--dash-accent) 30%, #0b1020);
}
</style>