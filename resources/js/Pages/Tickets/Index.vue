<script setup lang="js">
import BeMoLayout from '@/Layouts/BeMoLayout.vue';
import { useDebounce } from '@vueuse/core';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useConfirmDialog } from '@vueuse/core';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TicketCard from './Components/TicketCard.vue';
import { Link } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';

const useAddTicketDialog = useConfirmDialog();

const props = defineProps({
    ticket_status: Object
});

const tickets = ref([]);

const params = ref({
    search: '',
    filter: ''
})

const form = useForm({
    subject: '',
    body: ''
})

const isFilterOpen = ref(false);
const filterRef = ref(null);

onClickOutside(filterRef, () => { isFilterOpen.value = false; });

/* Choose a filter option, close, then fetch */
const selectFilter = (value) => {
    params.value.filter = value;
    isFilterOpen.value = false;
    fetchTickets();
};


const fetchTickets = async (page = 1) => {
    try {
        const response = await axios.get(route('api.tickets.index'), { params: { ...params.value, page } });
        tickets.value = response.data;
    } catch (error) {
        console.error('Error fetching tickets:', error);
    }
};

/**
 * Navigate to a pagination link without letting Inertia try to follow the API URL.
 * Extracts the `page` query param from the API link and fetches that page client-side.
 */
const goToPage = (link) => {
    if (!link?.url) return;
    try {
        const u = new URL(link.url);
        const p = u.searchParams.get('page') ?? '1';
        fetchTickets(Number(p));
    } catch (e) {
        console.error('Invalid pagination link', e);
    }
};

/**
 * Handle search input changes
 */
const onSearch = useDebounce(() => {
    try {
        fetchTickets();
    } catch (error) {
        console.error('Error during search:', error);
    }
}, 300)


/**
 * Handle new ticket creation
 */
const addNewTicket = async () => {
    if (!useAddTicketDialog.isRevealed.value) {
        form.reset();
        form.clearErrors?.();
        useAddTicketDialog.reveal();
        return;
    }

    try {
        await axios.post(route('api.tickets.store'), { ...form });
        useAddTicketDialog.confirm();
        form.reset();
        fetchTickets(); 
    } catch (error) {
        if (error.response?.data?.errors) {
            form.setError?.(error.response.data.errors);
        } else {
            console.error('An unexpected error occurred:', error);
        }
    }
};

onMounted(() => {
    fetchTickets();
});

</script>

<template>
    <Head title="Tickets" />

    <!-- Modal for adding a new ticket -->
    <Modal :show="useAddTicketDialog.isRevealed.value" @close="useAddTicketDialog.cancel()">
        <div class="modal__header">
            <h2 class="modal__title">Add Ticket</h2>
        </div>

        <div class="modal__body">
            <label class="modal__label">
                <label class="modal__label">Subject</label>
                <input v-model="form.subject" class="modal__input" required />
                <small v-if="form.errors.subject" class="modal__error">
                    {{ Array.isArray(form.errors.subject) ? form.errors.subject[0] : form.errors.subject }}
                </small>
            </label>

            <label class="modal__label">
                <label class="modal__label">Body</label>
                <textarea v-model="form.body" class="modal__textarea" required></textarea>
                <small v-if="form.errors.body" class="modal__error">
                    {{ Array.isArray(form.errors.body) ? form.errors.body[0] : form.errors.body }}
                </small>
            </label>

            <div class="modal__footer">
                <button type="button" class="modal__btn modal__btn--secondary" @click="useAddTicketDialog.cancel()">
                    Cancel
                </button>
                <!-- call the same function; no native submit, no direct confirm -->
                <button type="button" class="modal__btn modal__btn--primary" @click="addNewTicket">
                    Create
                </button>
            </div>
        </div>
    </Modal>



    <BeMoLayout>
        <div class="ticket-list">
            <h2 class="ticket-list__title">Tickets</h2>

            <div class="ticket-list__controls">
                <div class="ticket-list__item ticket-list__item--search">
                    <label for="searchbar" class="ticket-list__label">Search for a City</label>
                    <input type="text" id="searchbar"
                        class="ticket-list__input"
                        placeholder="Search by name or content of the ticket..." v-model="params.search" @keyup="onSearch" />
                </div>

                <div class="ticket-list__item ticket-list__item--filter" v-if="ticket_status.length">
                    <div class="filter__label">Status</div>

                    <!-- attach ref for outside-click, and control open state -->
                    <div class="filter__dropdown" ref="filterRef">
                        <button type="button" class="filter__toggle" :aria-expanded="isFilterOpen" aria-haspopup="listbox"
                            @click="isFilterOpen = !isFilterOpen" @keydown.escape.prevent.stop="isFilterOpen = false">
                            <span class="filter__selected">
                                {{(ticket_status.find(s => s.value === params.filter) || { label: 'All statuses' }).label}}
                            </span>
                            <span class="filter__caret">▾</span>
                        </button>

                        <!-- only render menu when open -->
                        <ul v-if="isFilterOpen" class="filter__menu" role="listbox" tabindex="-1">
                            <li class="filter__item" role="option" @click="selectFilter('')">
                                All statuses
                            </li>

                            <li v-for="s in ticket_status" :key="s.value" class="filter__item" role="option"
                                :aria-selected="params.filter === s.value" @click="selectFilter(s.value)">
                                <span class="filter__item-label">{{ s.label }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="ticket-list__item ticket-list__item--action">
                    <button type="button" class="ticket-list__add" @click="addNewTicket">Add New Ticket</button>
                </div>
            </div>
        </div>



        <section class="tickets">
            <div v-if="tickets?.data?.length" class="tickets__grid" role="list">
                <div v-for="ticket in tickets.data" :key="ticket.id" class="tickets__item" role="listitem">
                    <TicketCard :ticket="ticket" @refetch="fetchTickets" />
                </div>
            </div>

            <div v-else class="tickets__empty">
                No tickets found.
            </div>
        </section>
        <nav v-if="tickets?.links?.length" class="pagination" aria-label="Pagination navigation">
            <ul class="pagination__list">
                <li v-for="link in tickets?.links" :key="link?.label" class="pagination__item">
                    <button v-if="link?.url" type="button" class="pagination__link"
                        :class="{ 'pagination__link--active': link?.active }" @click="goToPage(link)"
                        v-html="link?.label">
                    </button>

                    <span v-else class="pagination__label">
                        <span v-html="link?.label"></span>
                    </span>
                </li>
            </ul>
        </nav>
    </BeMoLayout>
</template>

<style scoped>
/* grid container (BEM) */
.tickets {
    margin-top: 1rem;
}

/* 3x3 grid on desktop; responsive down to 2 / 1 columns */
.tickets__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    align-items: start;
}

/* each grid item is a flex wrapper so the card can grow to full height */
.tickets__item {
    display: flex;
    min-height: 0;
    /* allow children to shrink properly */
}

/* empty state */
.tickets__empty {
    color: #6b7280;
    padding: 1rem;
    font-weight: 600;
}

/* responsive breakpoints */
@media (max-width: 1024px) {
    .tickets__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .tickets__grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

.modal__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
    color: #111827;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.modal__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: inherit;
}

.modal__body {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #ffffff;
    color: #111827;
}

.modal__label {
    display: block;
}

/* inner label used for the field caption (markup nests labels) */
.modal__label>.modal__label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
    color: #111827;
    font-size: 0.875rem;
}

.modal__error {
    color: #dc2626;
    margin-top: 0.25rem;
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
}

.modal__input,
.modal__textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    background: #ffffff;
    /* white fields with dark text */
    color: #111827;
    border-radius: 0.375rem;
    box-sizing: border-box;
}

.modal__input:focus,
.modal__textarea:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
    border-color: #6366f1;
}

.modal__textarea {
    min-height: 100px;
    resize: vertical;
}

.modal__footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    background: #f9fafb;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

.modal__btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    font-weight: 600;
    cursor: pointer;
}

.modal__btn--secondary {
    background: #ffffff;
    color: #111827;
    border-color: #d1d5db;
}

.modal__btn--primary {
    background: #111827;
    color: #ffffff;
    border-color: #111827;
}

.modal__btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (min-width: 640px) {
    .modal__body {
        padding: 1.25rem 1.5rem;
    }
}

/* Pagination (BEM) */
.pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.pagination__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    list-style: none;
    padding: 0;
    margin: 0;
    align-items: center;
    justify-content: center;
}

.pagination__item {
    margin: 0.25rem 0;
}

.pagination__link,
.pagination__label {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border: 1px solid #e5e7eb;
    /* gray-200 - lighter border to match other UI */
    border-radius: 0.375rem;
    font-size: 0.875rem;
    /* text-sm */
    color: #6b7280;
    /* gray-500 - consistent with other text */
    text-decoration: none;
    background: #ffffff;
}

.pagination__link:hover {
    background-color: #f3f4f6;
    /* gray-100 */
}

.pagination__link:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    /* subtle focus ring */
}

.pagination__link--active {
    background-color: #2563eb;
    /* blue-600 */
    color: #ffffff;
    border-color: #2563eb;
}

.pagination__label {
    color: #9ca3af;
    /* gray-400 - for disabled labels */
    background: transparent;
    border-color: transparent;
    cursor: default;
    opacity: 0.6;
    pointer-events: none;
    /* ensure it's not interactive */
}

/* Filter dropdown (BEM) */
.filter {
    margin-bottom: 1rem;
    display: flex;
    gap: 1rem;
    align-items: center;
}

.filter__label {
    font-weight: 700;
    color: #111827;
}

.filter__dropdown {
    position: relative;
}

.filter__toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    border-radius: 0.375rem;
    cursor: pointer;
}

.filter__selected {
    color: #374151;
    font-weight: 600;
}

.filter__caret {
    color: #6b7280;
    font-size: 0.85rem;
}

.filter__menu {
    position: absolute;
    left: 0;
    top: calc(100% + 0.5rem);
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 6px 12px rgba(16, 24, 40, 0.08);
    min-width: 200px;
    z-index: 30;
    padding: 0.25rem 0;
}

.filter__item {
    padding: 0.5rem 0.75rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    cursor: pointer;
    color: #374151;
}

.filter__item:hover {
    background: #f3f4f6;
}

.filter__item-label {
    color: #6b7280;
    font-size: 0.9rem;
}

/* Ticket list block: row layout for search, filter, and action */
.ticket-list {
    margin-bottom: 1rem;
}

.ticket-list__title {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.ticket-list__controls {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.ticket-list__item {
    display: flex;
    flex-direction: column;
}

.ticket-list__item--search {
    flex: 1 1 0%;
}

.ticket-list__label {
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.25rem;
}

.ticket-list__input {
    width: 100%;
    padding: 0.75rem;
    background: #ffffff;
    color: black;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-sizing: border-box;
}

.ticket-list__input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
}

.ticket-list__item--filter {
    flex: 0 0 240px;
}

.ticket-list__item--action {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

.ticket-list__add {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    background: #111827;
    color: #ffffff;
    font-weight: 600;
    cursor: pointer;
}

.ticket-list__add:hover {
    background: #0b1220;
}

/* Active modifier for list items (BEM) */
.ticket-list__item--active {
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    border-radius: 0.5rem;
    background: linear-gradient(180deg, rgba(99,102,241,0.03), transparent);
}

/* Controls responsive tweaks */
@media (min-width: 768px) {
    .ticket-list__controls {
        align-items: center;
        gap: 1.25rem;
    }
}

@media (max-width: 640px) {
    .ticket-list__controls {
        flex-direction: column;
        align-items: stretch;
    }

    .ticket-list__item--filter {
        flex: none;
        width: 100%;
    }

    .ticket-list__item--action {
        justify-content: flex-start;
    }
}
</style>