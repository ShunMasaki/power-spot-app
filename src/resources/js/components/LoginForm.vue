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
            <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
            <button type="submit">ログイン</button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { signIn } from 'aws-amplify/auth'

const email = ref('')
const password = ref('')
const nickname = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
    error.value = ''
    loading.value = true

    if (!email.value || !password.value || !nickname.value) {
        error.value = 'すべての項目を入力してください'
        loading.value = false
        return
    }

    try {
        const result = await signIn({ username: emmail.value, password: password.value })
        console.log('ログイン成功:', result)
        window.location.reload()
        // TODO: ニックネームの保存は初回登録時に設定する想定。
    } catch (error) {
        console.error('ログインエラー:', error)
        errorMessage.value = 'ログインに失敗しました。メールアドレスとパスワードを確認してください。'
    } finally {
        loading.value = false
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
