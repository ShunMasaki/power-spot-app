<template>
    <div v-if="isOpen" class="modal-overlay" @click="close">
        <div class="modal-content" @click.stop>
            <button @click="close" class="close-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <!-- login-success を SpotDetail.vue に伝える -->
            <LoginForm ref="loginFormRef" @login-success="handleLoginSuccess" @switch-to-signup="switchToSignup" />
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import LoginForm from './LoginForm.vue'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    isOpen: Boolean,
})
const emit = defineEmits(['close', 'login-success', 'switch-to-signup'])

const loginFormRef = ref(null)

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

// モーダルが開かれた時にフォーカスを設定
watch(() => props.isOpen, async (isOpen) => {
    if (isOpen) {
        await nextTick()
        // 少し遅延してからフォーカスを設定（モーダルのアニメーション完了後）
        setTimeout(() => {
            if (loginFormRef.value && loginFormRef.value.focus) {
                loginFormRef.value.focus()
            }
        }, 100)
    }
})

</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5000; /* 最前面: 認証モーダル */
  animation: fadeIn 0.3s ease-out;
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 400px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  position: relative;
  animation: slideIn 0.3s ease-out;
}

.close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #333;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
