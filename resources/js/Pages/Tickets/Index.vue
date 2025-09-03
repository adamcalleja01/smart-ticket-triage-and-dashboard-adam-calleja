<script setup lang="js">
import BeMoLayout from '@/Layouts/BeMoLayout.vue';
import { useDebounce } from '@vueuse/core';
import axios from 'axios';
import { ref } from 'vue';
import { useConfirmDialog } from '@vueuse/core';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const useAddTicketDialog = useConfirmDialog();

const props = defineProps({
    ticket_status: Object
});

const params = ref({
    search: '',
    filter: []
})

const form = useForm({
    subject: '',
    body: ''
})

/**
 * Handle search input changes
 */
const onSearch = useDebounce(() => {

}, 300)


/**
 * Handle filter changes
 */
const onFilter = useDebounce(() => {

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

    // Modal is open → attempt submit
    try {
        await axios.post(route('api.tickets.store'), { ...form });
        useAddTicketDialog.confirm();
        form.reset();
    } catch (error) {
        if (error.response?.data?.errors) {
            form.setError?.(error.response.data.errors);
        } else {
            console.error('An unexpected error occurred:', error);
        }
    }
};

</script>

<template>
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
        <button @click="addNewTicket">Add New Ticket</button>
    </BeMoLayout>
</template>

<style scoped>
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
</style>