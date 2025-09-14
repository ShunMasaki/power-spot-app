<template>
    <div class="signup-form">
        <h2>新規ユーザー登録</h2>

        <!-- 登録フォーム -->
        <form v-if="!confirmationStep" @submit.prevent="handleSignUp">
            <div>
                <label for="email">メールアドレス</label>
                <input id="email" v-model="email" type="email" required />
            </div>
            <div>
                <label for="password">パスワード</label>
                <input id="password" v-model="password" type="password" required minlength="8" />
            </div>
            <div>
                <label for="nickname">ニックネーム</label>
                <input id="nickname" v-model="nickname" type="text" required minlength="2" />
            </div>
            <div v-if="error" class="error">{{ error }}</div>
            <button type="submit" :disabled="loading">
                {{ loading ? '登録中...' : '登録する' }}
            </button>
        </form>

        <!-- 確認コード入力フォーム -->
        <form v-else @submit.prevent="handleConfirmation">
            <div v-if="error" class="error">{{ error }}</div>
            <div>
                <label for="code">確認コード:</label>
                <input id="code" v-model="confirmationCode" type="text" required />
            </div>
            <div class="actions">
                <button type="submit" :disabled="loading">
                    {{ loading ? '確認中...' : '確認してログイン' }}
                </button>
                <button
                    type="button"
                    class="linklike"
                    @click="resend"
                    :disabled="resending || loading"
                >
                    {{ resending ? '再送中...' : 'コードを再送する' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { signUp, confirmSignUp, signIn, resendSignUpCode } from 'aws-amplify/auth'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const nickname = ref('')
const error = ref('')
const confirmationCode = ref('')
const confirmationStep = ref(false)
const loading = ref(false)
const resending = ref(false)
const auth = useAuthStore()

const emit = defineEmits(['signup-success'])

// サインアップ処理
const handleSignUp = async () => {
    error.value = ''
    loading.value = true
    try {
        await signUp({
            username: email.value.trim(),
            password: password.value,
            options: {
                userAttributes: {
                    email: email.value.trim(),
                    nickname: nickname.value
                },
            },
        })
        confirmationStep.value = true
    } catch (error) {
        error.value = toFriendlyError(error)
    } finally {
        loading.value = false
    }
}

// 確認コード入力処理
const handleConfirmation = async () => {
    error.value = ''
    loading.value = true
    try {
        await confirmSignUp({
            username: email.value.trim(),
            confirmationCode: confirmationCode.value
        })

        // 確認が成功したら自動ログイン
        const { isSignedIn } = await signIn({
            username: email.value.trim(),
            password: password.value
        })
        if (isSignedIn) {
            await auth.initializeAuth()
            emit('signup-success')
        }
    } catch (error) {
        error.value = toFriendlyError(error)
    } finally {
        loading.value = false
    }
}

// 確認コード再送信処理
const resend = async () => {
    error.value = ''
    resending.value = true
    try {
        await resendSignUpCode({ username: email.value.trim() })
    } catch (error) {
        error.value = toFriendlyError(error)
    } finally {
        resending.value = false
    }
}

const toFriendlyError = (error) => {
    const name = error.name || ''
    if (name === 'UsernameExistsException') return 'このメールアドレスは既に登録されています。'
    if (name === 'InvalidPasswordException') return 'パスワードポリシーを満たしていません。もう一度ご確認ください。'
    if (name === 'CodeMismatchException') return '確認コードが正しくありません。'
    if (name === 'ExpiredCodeException') return '確認コードの有効期限が切れています。再送してください。'
    if (name === 'LimitExceededException') return '試行回数の上限に達しました。しばらくしてからお試しください。'
    return error?.message || 'エラーが発生しました。時間をおいて再度お試しください。'
}
</script>

<style scoped>
.signup-form {
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
