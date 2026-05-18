<template>
    <div>
        <!-- Top Navbar -->
        <nav :class="['navbar', 'fixed-top', 'top-navbar', isNavOpen ? 'top-navbar--open' : 'top-navbar--collapsed']">
            <div class="container-fluid">
                <button id="MenuBar" @click="toggleNav" class="nav-toggle-btn" aria-label="Toggle sidebar">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="nav-right">
                    <span class="nav-brand">Factory Electric</span>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <div id="sideNav" :class="['side-nav', isNavOpen ? 'side-nav--open' : 'side-nav--closed']">
            <!-- Logo -->
            <div class="side-nav__top">
                <img alt="Factory Electric Logo" class="side-nav__logo" src="../Assets/img/factoryLogo.jpg" />
            </div>

            <!-- Nav Items -->
            <nav class="side-nav__menu">
                <Link href="/dashboardPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/dashboardPage') }">
                    <span class="side-nav__icon"><i class="fa fa-tachometer-alt"></i></span>
                    <span class="side-nav__caption">Dashboard</span>
                </Link>

                <Link href="/CustomerPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/CustomerPage') }">
                    <span class="side-nav__icon"><i class="fa fa-users"></i></span>
                    <span class="side-nav__caption">Clients</span>
                </Link>

                <Link href="/ProductPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/ProductPage') }">
                    <span class="side-nav__icon"><i class="fa fa-box"></i></span>
                    <span class="side-nav__caption">Products</span>
                </Link>

                <Link href="/create-sale" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/create-sale') }">
                    <span class="side-nav__icon"><i class="fa fa-shopping-cart"></i></span>
                    <span class="side-nav__caption">Quotes</span>
                </Link>

                <Link href="/InvoiceListPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/InvoiceListPage') }">
                    <span class="side-nav__icon"><i class="fa fa-file-invoice-dollar"></i></span>
                    <span class="side-nav__caption">Invoices</span>
                </Link>

                <Link href="/BankingPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/BankingPage') }">
                    <span class="side-nav__icon"><i class="fa fa-university"></i></span>
                    <span class="side-nav__caption">Banking</span>
                </Link>

                <Link href="/DeliveriesPage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/DeliveriesPage') }">
                    <span class="side-nav__icon"><i class="fa fa-truck"></i></span>
                    <span class="side-nav__caption">Deliveries</span>
                </Link>

                <Link href="/userProfilePage" class="side-nav__item" :class="{ 'side-nav__item--active': isActive('/userProfilePage') }">
                    <span class="side-nav__icon"><i class="fa fa-user-circle"></i></span>
                    <span class="side-nav__caption">Profile</span>
                </Link>
            </nav>

            <!-- Logout at bottom -->
            <div class="side-nav__footer">
                <Link href="/user-logout" class="side-nav__logout">
                    <span class="side-nav__icon"><i class="fa fa-sign-out-alt"></i></span>
                    <span class="side-nav__caption">Logout</span>
                </Link>
            </div>
        </div>

        <!-- Overlay for mobile -->
        <div
            v-if="isNavOpen && isMobile"
            class="side-nav__overlay"
            @click="toggleNav"
        ></div>

        <!-- Main Content -->
        <div :class="['main-content', isNavOpen ? 'main-content--shifted' : 'main-content--full']">
            <main>
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

// Responsive: close sidebar by default on small screens
const windowWidth = ref(window.innerWidth);
const isMobile = computed(() => windowWidth.value < 992);
const isNavOpen = ref(windowWidth.value >= 992);

function handleResize() {
    windowWidth.value = window.innerWidth;
    if (windowWidth.value >= 992) {
        isNavOpen.value = true;
    } else {
        isNavOpen.value = false;
    }
}

function toggleNav() {
    isNavOpen.value = !isNavOpen.value;
}

function isActive(href) {
    return window.location.pathname === href;
}

onMounted(() => window.addEventListener('resize', handleResize));
onBeforeUnmount(() => window.removeEventListener('resize', handleResize));
</script>

<style scoped>
/* ── Top Navbar ──────────────────────────────────────────────────────────── */
.top-navbar {
    background: #ffffff;
    height: 60px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: margin-left 0.3s ease;
    display: flex;
    align-items: center;
    z-index: 1030;
}
.top-navbar--open      { margin-left: 240px; }
.top-navbar--collapsed { margin-left: 0; }

.nav-toggle-btn {
    background: transparent;
    border: none;
    outline: none;
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    color: #374151;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
}
.nav-toggle-btn:hover {
    background: #f3f4f6;
    color: #21bf73;
}

.nav-right {
    margin-left: 14px;
}
.nav-brand {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    letter-spacing: 0.01em;
}

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
.side-nav {
    position: fixed;
    top: 0; left: 0;
    height: 100%;
    width: 240px;
    background: #1a1a2e;
    overflow-x: hidden;
    overflow-y: auto;
    transition: transform 0.3s ease;
    z-index: 1040;
    display: flex;
    flex-direction: column;
    scrollbar-width: thin;
    scrollbar-color: #2d2d44 #1a1a2e;
}
.side-nav--open   { transform: translateX(0);      }
.side-nav--closed { transform: translateX(-100%);  }

.side-nav::-webkit-scrollbar       { width: 4px; }
.side-nav::-webkit-scrollbar-track { background: #1a1a2e; }
.side-nav::-webkit-scrollbar-thumb { background: #2d2d44; border-radius: 4px; }

/* Logo area */
.side-nav__top {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 20px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.side-nav__logo {
    width: 72px; height: 72px;
    border-radius: 16px;
    object-fit: cover;
    border: 2px solid rgba(33, 191, 115, 0.3);
}

/* Menu */
.side-nav__menu {
    flex: 1;
    padding: 14px 10px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

/* Nav item */
.side-nav__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
    font-weight: 500;
    transition: background 0.18s, color 0.18s;
}
.side-nav__item:hover {
    background: rgba(33, 191, 115, 0.12);
    color: #ffffff;
}
.side-nav__item--active {
    background: rgba(33, 191, 115, 0.18);
    color: #21bf73;
    font-weight: 600;
}
.side-nav__item--active .side-nav__icon { color: #21bf73; }

.side-nav__icon {
    width: 20px;
    text-align: center;
    font-size: 15px;
    color: inherit;
    flex-shrink: 0;
}
.side-nav__caption {
    font-family: 'Poppins', sans-serif;
    letter-spacing: 0.01em;
}

/* Footer / Logout */
.side-nav__footer {
    padding: 12px 10px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.side-nav__logout {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.55);
    font-size: 13px;
    font-weight: 500;
    transition: background 0.18s, color 0.18s;
}
.side-nav__logout:hover {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* Mobile overlay */
.side-nav__overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1035;
}

/* ── Main Content ─────────────────────────────────────────────────────────── */
.main-content {
    margin-top: 60px;
    transition: margin-left 0.3s ease;
    min-height: calc(100vh - 60px);
    background: #f8f9fb;
}
.main-content--shifted { margin-left: 240px; }
.main-content--full    { margin-left: 0; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 991px) {
    .top-navbar--open      { margin-left: 0; }
    .top-navbar--collapsed { margin-left: 0; }
    .main-content--shifted { margin-left: 0; }
    .main-content--full    { margin-left: 0; }
}
</style>
