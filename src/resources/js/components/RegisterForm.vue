<template>
    <div class="register-form">
        <h2>新規登録</h2>
        <form @submit.prevent="handleRegister">
            <div>
            <label for="email">メールアドレス</label>
            <input id="email" v-model="form.email" type="email" required />
            </div>

            <div>
            <label for="password">パスワード</label>
            <input id="password" v-model="form.password" type="password" required />
            </div>

            <div>
            <label for="nickname">ニックネーム</label>
            <input id="nickname" v-model="form.nickname" type="text" required />
            </div>

            <button type="submit">登録</button>
        </form>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { signUp } from 'aws-amplify/auth'

const form = ref({
    email: '',
    password: '',
    nickname: ''
})

const handleRegister = async () => {
    errorMessage.value = ''

    try {
        await signUp({
            username: form.value.password,
            options: {
                userAttributes: {
                    email: form.value.email,
                    nickname: form.value.nickname
                }
            }
        })
        alert('登録メールを送信しました。メールを確認してください。')
    } catch (error) {
        console.error('登録エラー:', error)
        errorMessage.value = error.message || '登録に失敗しました。'
    }
}
</script>

<style scoped>
.register-form {
  max-width: 400px;
  margin: auto;
}
.error {
  color: red;
  margin-top: 10px;
}
</style>
