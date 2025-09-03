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
    const { data, isCanceled } = await useAddTicketDialog.reveal();

    if (isCanceled) return;

    form.post(route('api.tickets.store'), {
        onSuccess: () => {
            console.log('Ticket created successfully');
            form.reset();
        },
        onError: (errors) => {
            console.error('Error creating ticket:', errors);
        }
    });
}

</script>

<template>
    <Modal :show="useAddTicketDialog.isRevealed.value" @close="useAddTicketDialog.cancel()">
        
    </Modal>

    <BeMoLayout>
        <button @click="addNewTicket">Add New Ticket</button>
    </BeMoLayout>
</template>