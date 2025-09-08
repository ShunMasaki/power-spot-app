<template>
  <div class="login-form">
    <h2>ログイン</h2>
    <form @submit.prevent="handleLogin">
      <div>
        <label for="email">メールアドレス:</label>
        <input id="email" v-model="email" type="email" required />
      </div>
      <div>
        <label for="password">パスワード:</label>
        <input id="password" v-model="password" type="password" required />
      </div>

      <!-- エラーメッセージ -->
      <div v-if="auth.error" class="error">{{ auth.error }}</div>

      <!-- ログインボタン -->
      <button type="submit">
        ログイン
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const emit = defineEmits(['login-success'])
const email = ref('')
const password = ref('')
const auth = useAuthStore()

const handleLogin = async () => {
    await auth.login(email.value, password.value)
    if (auth.isLoggedIn) {
        emit('login-success')
    }
}
</script>

<style scoped>
.login-form {
  padding: 1em;
  border: 1px solid #ccc;
  border-radius: 8px;
  max-width: 400px;
  margin: auto;
}
.error {
  color: red;
  margin-top: 0.5em;
}
</style>
