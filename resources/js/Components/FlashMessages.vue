<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const showSuccess = ref(false);
const showError = ref(false);

// Watch for flash messages
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        }
        if (flash?.error) {
            showError.value = true;
            setTimeout(() => {
                showError.value = false;
            }, 5000);
        }
    },
    { deep: true, immediate: true }
);

const closeSuccess = () => {
    showSuccess.value = false;
};

const closeError = () => {
    showError.value = false;
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <!-- Success Message -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 translate-y-2"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform opacity-100"
            leave-to-class="transform opacity-0"
        >
            <div
                v-if="showSuccess && page.props.flash?.success"
                class="alert alert-success shadow-lg max-w-md"
            >
                <div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current flex-shrink-0 h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>{{ page.props.flash.success }}</span>
                </div>
                <button @click="closeSuccess" class="btn btn-sm btn-ghost">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </transition>

        <!-- Error Message -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 translate-y-2"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform opacity-100"
            leave-to-class="transform opacity-0"
        >
            <div
                v-if="showError && page.props.flash?.error"
                class="alert alert-error shadow-lg max-w-md"
            >
                <div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current flex-shrink-0 h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>{{ page.props.flash.error }}</span>
                </div>
                <button @click="closeError" class="btn btn-sm btn-ghost">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </transition>
    </div>
</template>

