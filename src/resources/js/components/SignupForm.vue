<template>
    <div class="signup-form">
        <h2>新規ユーザー登録</h2>

        <!-- 機能案内 -->
        <div class="features-section">
            <h3>登録したらこんなことができますよ</h3>
            <div class="features-list">
                <div class="feature-item">
                    <img :src="pencilIcon" alt="レビュー" class="feature-icon" />
                    <span>レビュー投稿！</span>
                </div>
                <div class="feature-item">
                    <img :src="goodIcon" alt="お気に入り" class="feature-icon" />
                    <span>スポットお気に入り登録！</span>
                </div>
                <div class="feature-item">
                    <img :src="cameraIcon" alt="おみくじ" class="feature-icon" />
                    <span>おみくじアップロード！（My おみくじ）</span>
                </div>
                <div class="feature-item">
                    <img :src="shrineIcon" alt="御朱印" class="feature-icon" />
                    <span>御朱印アップロード！（My 御朱印帳）</span>
                </div>
            </div>
        </div>

        <!-- 登録フォーム -->
        <form v-if="!confirmationStep" @submit.prevent="handleSignUp">
            <div class="form-group">
                <label for="email">メールアドレス *</label>
                <input
                    id="email"
                    v-model="email"
                    type="email"
                    required
                    placeholder="example@email.com"
                    class="form-input"
                />
            </div>

            <div class="form-group">
                <label for="password">パスワード *</label>
                <div class="password-input-wrapper">
                    <input
                        id="password"
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        minlength="8"
                        class="form-input"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="password-toggle"
                    >
                        <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="nickname">ニックネーム *</label>
                <input
                    id="nickname"
                    v-model="nickname"
                    type="text"
                    required
                    minlength="2"
                    placeholder="パワスポ太郎"
                    class="form-input"
                />
            </div>

            <div v-if="error" class="error-message">{{ error }}</div>

            <button type="submit" :disabled="loading" class="signup-btn">
                {{ loading ? '登録中...' : '登録する' }}
            </button>
        </form>

        <!-- 確認コード入力フォーム -->
        <form v-else @submit.prevent="handleConfirmation">
            <div v-if="error" class="error-message">{{ error }}</div>
            <div class="form-group">
                <label for="code">確認コード *</label>
                <input
                    id="code"
                    v-model="confirmationCode"
                    type="text"
                    required
                    placeholder="123456"
                    class="form-input"
                />
            </div>
            <div class="actions">
                <button type="submit" :disabled="loading" class="confirm-btn">
                    {{ loading ? '確認中...' : '確認してログイン' }}
                </button>
                <button
                    type="button"
                    class="resend-btn"
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
import pencilIcon from '../assets/icons/pencil.png'
import goodIcon from '../assets/icons/good.png'
import cameraIcon from '../assets/icons/camera.png'
import shrineIcon from '../assets/icons/shrine.png'

const email = ref('')
const password = ref('')
const nickname = ref('')
const error = ref('')
const confirmationCode = ref('')
const confirmationStep = ref(false)
const loading = ref(false)
const resending = ref(false)
const showPassword = ref(false)
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
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
  padding: 0;
}


h2 {
  text-align: center;
  font-size: 18px;
  font-weight: 400;
  color: #333;
  margin: 0 0 12px 0;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-group {
  margin-bottom: 12px;
}

label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #333;
  margin-bottom: 6px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 15px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #e91e63;
  box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
}

.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: #333;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  margin: 8px 0 16px 0;
  padding: 8px 12px;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 6px;
}

.signup-btn, .confirm-btn {
  width: 100%;
  background: #e91e63;
  color: white;
  border: none;
  padding: 12px 18px;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  margin-bottom: 16px;
}

.signup-btn:hover, .confirm-btn:hover {
  background: #c2185b;
  transform: translateY(-1px);
}

.signup-btn:active, .confirm-btn:active {
  transform: translateY(0);
}

.signup-btn:disabled, .confirm-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.resend-btn {
  background: none;
  border: none;
  color: #e91e63;
  text-decoration: underline;
  cursor: pointer;
  font-size: 14px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: color 0.2s ease;
  padding: 8px;
}

.resend-btn:hover {
  color: #c2185b;
}

.resend-btn:disabled {
  color: #ccc;
  cursor: not-allowed;
}

/* 機能案内セクション */
.features-section {
  margin-bottom: 12px;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.features-section h3 {
  text-align: center;
  font-size: 13px;
  font-weight: 500;
  color: #333;
  margin: 0 0 8px 0;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.features-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 2px 0;
}

.feature-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.feature-item span {
  font-size: 11px;
  color: #555;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 400;
  line-height: 1.2;
}
</style>
