<template>
    <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 rounded-lg shadow-lg max-w-sm w-full relative">
            <button @click="close" class="absolute top-2 right-2 text-gray-500">x</button>
            <!-- signup-success を SpotDetail.vue に伝える -->
            <SignupForm @signup-success="handleSignupSuccess" />
            <div class="mt-3 text-sm text-center">
                すでにアカウントをお持ちですか？
                <button type="button" class="underline" @click="switchToLogin">ログインへ</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import SignupForm from './SignupForm.vue'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    isOpen: Boolean,
})
const emit = defineEmits(['close', 'signup-success', 'switch-to-login'])

const switchToLogin = () => {
    emit('switch-to-login')
}

const close = () => {
    emit('close')
}

const handleSignupSuccess = () => {
    emit('signup-success')
    close()
}

</script>
