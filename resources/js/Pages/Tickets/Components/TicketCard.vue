<script setup lang="js">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useConfirmDialog } from '@vueuse/core';

const useEditTicketDialog = useConfirmDialog();

const props = defineProps({
    ticket: Object
})

const form = useForm({
    status: props?.ticket?.status ?? '',
    category: props?.ticket?.category ?? '',
    note: props?.ticket?.note ?? '',
})

const emit = defineEmits(['refetch'])

const isClassifying = ref(false)

const updateTicket = async () => {
    const {data, isCanceled} = await useEditTicketDialog.reveal();

    if (isCanceled) {
        return;
    }


}

const classifyTicket = async () => {
    try {
        isClassifying.value = true
        const response = await axios.post(route('api.tickets.classify', {
            ticket: props?.ticket?.id
        }))
        console.log('Ticket classified:', response.data)
        emit('refetch')
    } catch (error) {
        console.error('Error classifying ticket:', error);
    } finally {
        isClassifying.value = false
    }
}

</script>

<template>
    <article class="ticket-card" :aria-label="`Ticket ${ticket.id}`">
        <header class="ticket-card__header">
            <h3 class="ticket-card__subject">{{ ticket.subject }}</h3>
            <span class="ticket-card__status" :class="{
                'ticket-card__status--open': ticket.status === 'open',
                'ticket-card__status--closed': ticket.status === 'closed'
            }">
                {{ ticket.status ?? 'unknown' }}
            </span>
        </header>

        <div class="ticket-card__meta">
            <time class="ticket-card__time" :datetime="ticket.created_at">
                {{ ticket.created_at ? new Date(ticket.created_at).toLocaleString() : '' }}
            </time>

            <!-- Category label -->
            <span class="ticket-card__category" v-if="ticket.category" :title="`Category: ${ticket.category}`">
                {{ ticket.category }}
            </span>

            <!-- Confidence label (inline formatting) -->
            <span class="ticket-card__confidence" v-if="ticket.confidence !== null && ticket.confidence !== undefined && ticket.confidence !== ''">
                Confidence: <strong>{{ Number(ticket.confidence).toFixed(2) }}</strong>
            </span>

            <!-- Explanation tooltip/icon -->
            <button
                class="ticket-card__explain-btn"
                v-if="ticket.explanation"
                :title="ticket.explanation"
                type="button"
                aria-label="Explanation"
            >
                
                <!-- simple info icon -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="#eef2ff"/>
                    <path d="M11 10h2v6h-2v-6zm0-4h2v2h-2V6z" fill="#4f46e5"/>
                </svg>
            </button>

            <!-- Note badge if present -->
            <span class="ticket-card__note-badge" v-if="ticket.note">
                Note
            </span>
        </div>

        <div class="ticket-card__body">
            <p class="ticket-card__excerpt">{{ ticket.body ?? 'No description available' }}</p>
            <p class="ticket-card__explanation" v-if="ticket.explanation">
                <strong>Explanation:</strong> {{ ticket.explanation }}
            </p>
        </div>

        <footer class="ticket-card__footer">
            <button class="ticket-card__btn" type="button" @click="updateTicket">
                Update
            </button>
            <button
                class="ticket-card__btn ticket-card__btn--secondary"
                type="button"
                @click="classifyTicket"
                :disabled="isClassifying"
                :aria-busy="isClassifying"
            >
                <span v-if="isClassifying" class="ticket-card__spinner" aria-hidden="true"></span>
                <span v-if="isClassifying">Classifying...</span>
                <span v-else>Classify</span>
            </button>
            <Link :href="route('tickets.show', {
                ticket: ticket.id
            })" class="ticket-card__link">View</Link>
        </footer>
    </article>
</template>

<style>
.ticket-card {
    height: 100%;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.03);
    color: #111827;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.ticket-card:hover {
    transform: translateY(-4px);
    transition: transform 180ms ease, box-shadow 180ms ease;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.ticket-card:focus-within {
    outline: none;
    box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
}

/* Header */
.ticket-card__header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 0.5rem;
}

.ticket-card__subject {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.ticket-card__status {
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    text-transform: capitalize;
    color: #ffffff;
}

.ticket-card__status--open {
    background: #16a34a;
    /* green */
}

.ticket-card__status--closed {
    background: #ef4444;
    /* red */
}

/* Meta row */
.ticket-card__meta {
    display: flex;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    align-items: center;
    flex-wrap: wrap;
}

.ticket-card__time {
    display: inline-block;
}

.ticket-card__category {
    background: #f3f4f6;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-weight: 600;
    color: #374151;
}

.ticket-card__confidence {
    background: transparent;
    color: #6b7280;
    font-weight: 600;
}

.ticket-card__explain-btn {
    background: transparent;
    border: none;
    padding: 0;
    margin-left: 0.25rem;
    display: inline-flex;
    align-items: center;
    cursor: help;
}

.ticket-card__note-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 0.125rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 700;
    margin-left: 0.5rem;
    font-size: 0.75rem;
}

/* Active modifier for card used when a list item is active */
.ticket-card--active {
    border-color: rgba(99,102,241,0.5);
    box-shadow: 0 8px 28px rgba(99,102,241,0.06);
}

/* Body */
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
    border: 2px solid rgba(0,0,0,0.15);
    border-top-color: rgba(0,0,0,0.6);
    border-radius: 50%;
    margin-right: 0.5rem;
    animation: ticket-spin 0.8s linear infinite;
}

@keyframes ticket-spin {
    to { transform: rotate(360deg); }
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