<template>
    <div class="confirm-code-form">
        <h2>確認コードを入力してください</h2>

        <p>登録されたメールアドレス宛に確認コードが送信されました。</p>

        <input
        v-model="code"
        type="text"
        placeholder="確認コード"
        class="code-input"
        />

        <button @click="handleConfirm" class="confirm-button">確認してログイン</button>

        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { confirmSignUp, signIn } from 'aws-amplify/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const code = ref('')
const errorMessage = ref('')

const props = defineProps({
    email: { type: String, required: true },
    password: { type: String, required: true }
})

const handleConfirm = async () => {
    errorMessage.value = ''

    try {
        await confirmSignUp({
            username: props.email,
            confirmationCode: code.value
        })

        await signIn({
            username: props.email,
            password: props.password
        })
        router.push('/')
    } catch (error) {
        console.error('確認エラー:', error)
        errorMessage.value = error.message || '確認に失敗しました。'
    }
}
</script>

<style scoped>
.confirm-code-form {
  max-width: 400px;
  margin: auto;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.code-input {
  width: 100%;
  padding: 0.5rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
  font-size: 1rem;
}

.confirm-button {
  padding: 0.5rem 1rem;
  font-size: 1rem;
  cursor: pointer;
}

.error-message {
  color: red;
  margin-top: 0.5rem;
}
</style>
