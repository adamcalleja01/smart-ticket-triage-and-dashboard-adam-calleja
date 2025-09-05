<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const panel = ref();
const showSlot = ref(props.show);

const onDocumentMouseDown = (e) => {
    if (!props.show || !props.closeable) return;

    // If the click target is not inside the modal panel, close the modal
    if (panel.value && !panel.value.contains(e.target)) {
        close();
    }
};

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();

            document.addEventListener('mousedown', onDocumentMouseDown);
        } else {
            document.body.style.overflow = '';

            document.removeEventListener('mousedown', onDocumentMouseDown);

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'modal__container--sm',
        md: 'modal__container--md',
        lg: 'modal__container--lg',
        xl: 'modal__container--xl',
        '2xl': 'modal__container--2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog class="modal" ref="dialog">
        <div class="modal__overlay" scroll-region>
            <Transition enter-active-class="modal__backdrop-transition--enter-active"
                enter-from-class="modal__backdrop-transition--enter-from"
                enter-to-class="modal__backdrop-transition--enter-to"
                leave-active-class="modal__backdrop-transition--leave-active"
                leave-from-class="modal__backdrop-transition--leave-from"
                leave-to-class="modal__backdrop-transition--leave-to">
                <div v-show="show" class="modal__backdrop" @click="close">
                    <div class="modal__backdrop-fade" />
                </div>
            </Transition>

            <Transition enter-active-class="modal__panel-transition--enter-active"
                enter-from-class="modal__panel-transition--enter-from"
                enter-to-class="modal__panel-transition--enter-to"
                leave-active-class="modal__panel-transition--leave-active"
                leave-from-class="modal__panel-transition--leave-from"
                leave-to-class="modal__panel-transition--leave-to">
                <div v-show="show" class="modal__container" :class="maxWidthClass" ref="panel">
                    <slot v-if="showSlot" />
                </div>
            </Transition>
        </div>
    </dialog>
</template>

<style scoped>
/* Modal base */
.modal {
    z-index: 5000;
    margin: 0;
    min-height: 100%;
    min-width: 100%;
    overflow-y: auto;
    background: transparent;
}

.modal__overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    /* overlay base */
    overflow-y: auto;
    padding: 2rem 1rem;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Backdrop */
.modal__backdrop {
    position: fixed;
    inset: 0;
    display: block;
    z-index: 1000;
    /* backdrop sits on overlay base */
}

.modal__backdrop-fade {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
}

/* Backdrop transitions (used by <Transition> props) */
.modal__backdrop-transition--enter-active {
    transition: opacity 300ms ease-out;
}

.modal__backdrop-transition--leave-active {
    transition: opacity 200ms ease-in;
}

.modal__backdrop-transition--enter-from,
.modal__backdrop-transition--leave-to {
    opacity: 0;
}

.modal__backdrop-transition--enter-to,
.modal__backdrop-transition--leave-from {
    opacity: 1;
}

/* Panel / container */
.modal__container {
    margin-bottom: 1.5rem;
    transform: none;
    overflow: hidden;
    border-radius: 0.5rem;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    transition: transform 300ms ease-out, opacity 300ms ease-out;
    width: 100%;
    box-sizing: border-box;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    z-index: 1100;
    /* container sits above backdrop */
    max-height: calc(100vh - 4rem);
    overflow: auto;
}

/* Max width modifiers (BEM --modifier) */
.modal__container--sm {
    max-width: 24rem;
}

.modal__container--md {
    max-width: 28rem;
}

.modal__container--lg {
    max-width: 32rem;
}

.modal__container--xl {
    max-width: 36rem;
}

.modal__container--2xl {
    max-width: 42rem;
}

/* Panel transitions (used by <Transition> props) */
.modal__panel-transition--enter-active {
    transition: opacity 300ms ease-out, transform 300ms ease-out;
}

.modal__panel-transition--leave-active {
    transition: opacity 200ms ease-in, transform 200ms ease-in;
}

.modal__panel-transition--enter-from,
.modal__panel-transition--leave-to {
    opacity: 0;
    transform: translateY(1rem) scale(0.95);
}

.modal__panel-transition--enter-to,
.modal__panel-transition--leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}
</style>

<style scoped>
/* Content styles for pages using the modal slot (BEM) */
.modal__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e6e6e6;
}

.modal__title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #000000;
    line-height: 1.2;
}

.modal__body {
    padding: 1rem 1.25rem;
    display: block;
    color: #000000;
    font-size: 0.95rem;
}

.modal__label {
    display: block;
    margin-bottom: 0.85rem;
    font-size: 0.95rem;
    color: #000000;
}

.modal__input,
.modal__textarea {
    display: block;
    width: 100%;
    padding: 0.6rem 0.9rem;
    border: 1px solid #cfcfcf;
    border-radius: 0.375rem;
    background: #ffffff;
    color: #000000;
    box-sizing: border-box;
    margin-top: 0.5rem;
    font-size: 0.95rem;
}

.modal__input:focus,
.modal__textarea:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
    border-color: #2563eb;
}

.modal__textarea {
    min-height: 6rem;
    resize: vertical;
}

.modal__footer {
    padding: 0.75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    border-top: 1px solid #e6e6e6;
}

.modal__btn {
    padding: 0.45rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    cursor: pointer;
}

.modal__btn--primary {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.modal__btn--primary:hover {
    background: #1e4cbf;
}

.modal__btn--secondary {
    background: #f3f4f6;
    color: #0f172a;
    border-color: #d1d5db;
}

.modal__btn--secondary:hover {
    background: #e5e7eb;
}

.modal__btn:disabled,
.modal__btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
