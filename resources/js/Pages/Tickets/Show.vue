<script setup lang="js">
import BeMoLayout from '@/Layouts/BeMoLayout.vue';
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useIntervalFn, tryOnScopeDispose } from '@vueuse/core';
import axios from 'axios';

const props = defineProps({
    ticket: Object,
    categories: Object,
    ticket_status: Object
})

const ticket = ref(null);

const form = useForm({
    status: props?.ticket?.status ?? '',
    category: props?.ticket?.category ?? '',
    note: props?.ticket?.note ?? '',
})

const preUpdatedAt = ref(props.ticket?.updated_at ? new Date(props.ticket.updated_at) : null)
const isClassifying = ref(false)

// component-level theme state: '' | 'dark' | 'light'
const theme = ref('')

/**
 * Fetch the ticket details from the backend API.
 */
const fetchTicket = async () => {
    try {
        const response = await axios.get(route('api.tickets.show', {
            ticket: props.ticket.id
        }), {
            // cache-buster + no-cache headers to ensure fresh data
            params: { _ts: Date.now() },
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                Pragma: 'no-cache',
                Expires: '0',
            },
        });
        ticket.value = response.data;
    } catch (error) {
        console.error('Error fetching ticket:', error);
    }
};

/**
 * Update the ticket using the backend API.
 */
const updateTicket = async () => {
    try {
        const response = await axios.patch(route('api.tickets.update', {
            ticket: props?.ticket?.id
        }), {
            ...form
        });
        console.log('Ticket updated:', response.data);
        fetchTicket();
    } catch (error) {
        console.error('Error updating ticket:', error);
    }
}


/**
 * Waits for the classification of a ticket to complete.
 * @param ticketId 
 * @param param1 
 */
const waitForClassification = (
    ticketId,
    {
        interval = 1200,
        timeout = 30000,
        // still allow content-based success, but also require updated_at to advance
        done = (t) => !!t?.explanation || t?.confidence != null || !!t?.category,
    } = {}
) => new Promise((resolve, reject) => {
    const start = Date.now()
    const { pause, resume } = useIntervalFn(async () => {
        try {
            const { data } = await axios.get(
                route('api.tickets.show', { ticket: ticketId }),
                {
                    params: { _ts: Date.now() }, // <- cache buster
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        Pragma: 'no-cache',
                        Expires: '0',
                    },
                }
            )

            const advanced =
                preUpdatedAt.value
                    ? new Date(data?.updated_at) > preUpdatedAt.value
                    : true

            if (advanced && done(data)) { pause(); return resolve(data) }

            if (Date.now() - start > timeout) {
                pause(); return reject(new Error('Classification timeout'))
            }
        } catch (e) {
            pause(); return reject(e)
        }
    }, interval, { immediate: false })

    tryOnScopeDispose(pause)
    resume()
})



/**
 * Classify the ticket using the backend API.
 */
const classifyTicket = async () => {
    isClassifying.value = true
    try {
        // remember the last updated_at we saw
        preUpdatedAt.value = ticket.value?.updated_at
            ? new Date(ticket.value.updated_at)
            : (props.ticket?.updated_at ? new Date(props.ticket.updated_at) : null)

        const res = await axios.post(route('api.tickets.classify', { ticket: props.ticket.id }))
        if (res.status < 200 || res.status >= 300) throw new Error(`Bad status ${res.status}`)

        await waitForClassification(props.ticket.id)

        // IMPORTANT: await the refetch so the next render has fresh data
        await fetchTicket()

        // (optional) sync the form with the freshly loaded ticket values
        form.status = ticket.value?.status ?? form.status
        form.category = ticket.value?.category ?? form.category
        form.note = ticket.value?.note ?? form.note
    } catch (e) {
        console.error('Classify error:', e)
    } finally {
        isClassifying.value = false
    }
}
onMounted(() => {
    fetchTicket();
});
</script>

<template>

    <Head title="Ticket Details" />

    <BeMoLayout>
        <section :class="['ticket-show', theme ? `ticket-show--${theme}` : '']">
            <header>
                <h1>{{ ticket?.subject }}</h1>
                <select id="status" class="ticket-show__status-select" v-model="form.status">
                    <option v-for="status in props.ticket_status" :key="status.value" :value="status.value">
                        {{ status.label }}
                    </option>
                </select>
                <p class="ticket-dates">Created: {{ new Date(ticket?.created_at).toLocaleString() }} &middot; Updated:
                    {{ new Date(ticket?.updated_at).toLocaleString() }}</p>
            </header>

            <section class="ticket-body">
                <p class="ticket-subject"><strong>Subject:</strong> {{ ticket?.subject }}</p>
                <div class="ticket-fullbody">
                    <div class="ticket-body-label"><strong>Body:</strong></div>
                    <div class="ticket-body-content">{{ ticket?.body }}</div>
                </div>
            </section>

            <section class="ticket-fields">
                <div class="ticket-fields__item ticket-fields__item--full">
                    <label for="category">Category</label>
                    <select id="category" v-model="form.category">
                        <option value="" disabled>Select a category</option>
                        <option v-for="cat in props.categories" :key="cat.value" :value="cat.value">
                            {{ cat.label }}
                        </option>
                    </select>
                </div>

                <div class="ticket-fields__item ticket-fields__item--full">
                    <label for="note">Note</label>
                    <textarea id="note" v-model="form.note" rows="4" placeholder="Add a note..."></textarea>
                </div>

                <div class="ticket-fields__item">
                    <label for="confidence">Confidence</label>
                    <p class="ticket-fields__value">{{ ticket?.confidence }} out of 1.0</p>
                </div>

                <div class="ticket-fields__item">
                    <label for="explanation">Explanation</label>
                    <p class="ticket-fields__value">{{ ticket?.explanation }}</p>
                </div>

                <div class="ticket-actions">
                    <button class="ticket-card__btn ticket-card__btn--secondary" type="button" @click="classifyTicket"
                        :disabled="isClassifying" :aria-busy="isClassifying">
                        <span v-if="isClassifying" class="ticket-card__spinner" aria-hidden="true"></span>
                        <span v-if="isClassifying">Classifying...</span>
                        <span v-else>Run Classification</span>
                    </button>

                    <button class="ticket-card__btn" type="button" @click="updateTicket">Save</button>
                </div>
            </section>
        </section>
    </BeMoLayout>
</template>

<style>
/* BEM: ticket-show block */
.ticket-show {
    max-width: 980px;
    margin: 2rem auto;
    padding: 1.5rem;
    background: #ffffff;
    border: 1px solid #e6e9ee;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    color: #0f172a;
    font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}

.ticket-show>header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.ticket-show h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.375rem;
    line-height: 1.15;
    font-weight: 800;
    color: #0b1220;
}

.ticket-show>header h4 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 9999px;
    text-transform: capitalize;
    color: #fff;
    background: #6b7280;
}

/* status specific colors if you later add modifiers */
.ticket-show>header h4:contains('open'),
.ticket-show>header h4[aria-status='open'] {
    background: #16a34a;
}

.ticket-show>header h4:contains('closed'),
.ticket-show>header h4[aria-status='closed'] {
    background: #ef4444;
}

.ticket-dates {
    margin: 0.25rem 0 0 0;
    color: #64748b;
    font-size: 0.9rem;
}

.ticket-body {
    background: #f8fafc;
    border: 1px solid #eef2ff;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.ticket-subject {
    margin: 0 0 0.5rem 0;
    color: #0f172a;
    font-weight: 700;
}

.ticket-fullbody {
    margin: 0;
    color: #334155;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ticket-body-label {
    font-weight: 700;
    color: #0f172a;
}

.ticket-body-content {
    white-space: pre-wrap;
    color: #334155;
    font-size: 0.95rem;
}

.ticket-fields {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1rem;
    align-items: start;
}

.ticket-fields__item {
    display: flex;
    flex-direction: column;
}

.ticket-fields__item--full {
    grid-column: 1 / -1;
}

.ticket-fields__value {
    margin: 0;
    color: #334155;
    padding: 0.45rem 0.6rem;
    background: #f8fafc;
    border: 1px solid #eef2ff;
    border-radius: 6px;
}

.ticket-fields label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #0f172a;
}

.ticket-fields select,
.ticket-fields textarea,
.ticket-fields input {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 1px solid #e6e9ee;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #0f172a;
    background: #fff;
    box-sizing: border-box;
}

/* status select in header — visually matches the category select */
.ticket-show__status-select {
    min-width: 180px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #e6e9ee;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #0f172a;
    background: #fff;
    box-sizing: border-box;
    appearance: none;
}

.ticket-show__status-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
}

.ticket-fields select:focus,
.ticket-fields textarea:focus,
.ticket-fields input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
}

.ticket-fields textarea[readonly],
.ticket-fields input[readonly] {
    background: #f8fafc;
    color: #374151;
}

.ticket-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 0.75rem;
    margin-top: 0.75rem;
    justify-content: flex-start;
    align-items: center;
}

.ticket-actions .ticket-card__btn {
    padding: 0.5rem 0.85rem;
}

.ticket-actions .ticket-card__btn--secondary {
    background: #fff;
    color: #111827;
    border: 1px solid #d1d5db;
}

@media (max-width: 820px) {
    .ticket-fields {
        grid-template-columns: 1fr;
    }

    .ticket-show>header {
        flex-direction: column;
        align-items: flex-start;
    }
}

.ticket-card__body {
    color: #374151;
    font-size: 0.95rem;
}

.ticket-card__excerpt {
    margin: 0 0 0.5rem 0;
    /* keep long excerpts tidy */
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    overflow: hidden;
}

.ticket-card__explanation {
    margin: 0;
    font-size: 0.875rem;
}

/* Footer / actions */
.ticket-card__footer {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-top: 0.5rem;
}

.ticket-card__btn {
    background: #111827;
    color: #fff;
    border: none;
    padding: 0.4rem 0.6rem;
    border-radius: 0.375rem;
    font-weight: 600;
    cursor: pointer;
}

.ticket-card__btn--secondary {
    background: #fff;
    color: #111827;
    border: 1px solid #d1d5db;
}

.ticket-card__btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

.ticket-card__spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(0, 0, 0, 0.15);
    border-top-color: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    margin-right: 0.5rem;
    animation: ticket-spin 0.8s linear infinite;
}

@keyframes ticket-spin {
    to {
        transform: rotate(360deg);
    }
}

.ticket-card__link {
    margin-left: auto;
    color: #4f46e5;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.875rem;
}

/* Small screens */
@media (min-width: 640px) {
    .ticket-card {
        padding: 1rem 1.25rem;
    }
}
</style>