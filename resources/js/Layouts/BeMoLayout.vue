<template>
    <div class="layout">
        <header class="layout__header">
            <div class="layout__brand">
                <img class="layout__brand-logo" src="https://assets.bemoacademicconsulting.com/files/AR8wdupQbJbHsczmjaMMjGId3cNRjmPLolORynZB.png" alt="BeMo Logo" />
                <h1 class="layout__brand-title">Ticket Triage System</h1>
            </div>
        </header>

        <main class="layout__content">
            <slot />
        </main>

        <footer class="layout__footer">
            <p class="layout__footer-text">© 2025 BeMo</p>
        </footer>
    </div>
</template>

<style scoped>
.layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: #f9fafb;
    color: #1f2937;
    position: relative;
}

.layout__header {
    background: #5c7fca;
    color: #fff;
    padding: 1rem;
}


.layout__header {
    background: #5c7fca;
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
    background: #e9eef6;
    padding: 1.25rem 1.5rem;
    text-align: center;
    margin-top: auto;
    width: 100%;
    box-shadow: 0 -6px 18px rgba(16,24,40,0.06);
}

.layout__footer-text {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #0f172a;
    letter-spacing: 0.01em;
}
</style>