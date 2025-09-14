<template>
    <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 rounded-lg shadow-lg max-w-sm w-full relative">
            <button @click="close" class="absolute top-2 right-2 text-gray-500">x</button>
            <!-- login-success を SpotDetail.vue に伝える -->
            <LoginForm @login-success="handleLoginSuccess" @switch-to-signup="switchToSignup" />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import LoginForm from './LoginForm.vue'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    isOpen: Boolean,
})
const emit = defineEmits(['close', 'login-success', 'switch-to-signup'])

const close = () => {
    emit('close')
}

const handleLoginSuccess = () => {
    emit('login-success')
    close()
}

const switchToSignup = () => {
    emit('switch-to-signup')
}

</script>
