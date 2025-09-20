<template>
  <div class="login-form">
    <h2>ログイン</h2>

    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">メールアドレス (あるいはID) *</label>
        <input
          id="email"
          v-model="email"
          type="email"
          required
          placeholder="st.shunmasaki@gmail.com"
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

      <!-- エラーメッセージ -->
      <div v-if="auth.error" class="error-message">{{ auth.error }}</div>

      <!-- ログインボタン -->
      <button type="submit" class="login-btn">
        ログイン
      </button>
    </form>

    <!-- ヘルプリンク -->
    <div class="help-links">
      <a href="#" class="help-link">パスワードを忘れた方</a>
    </div>

    <div class="signup-link">
      アカウントをお持ちでない方は
      <button type="button" class="signup-link-btn" @click="switchToSignup">
        <span>新規登録</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import pencilIcon from '../assets/icons/pencil.png'
import goodIcon from '../assets/icons/good.png'
import cameraIcon from '../assets/icons/camera.png'
import shrineIcon from '../assets/icons/shrine.png'

const emit = defineEmits(['login-success', 'switch-to-signup'])
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const auth = useAuthStore()

const handleLogin = async () => {
    // ログイン試行前にエラーメッセージをクリア
    auth.error = null
    await auth.login(email.value, password.value)
    if (auth.isLoggedIn) {
        emit('login-success')
    }
}

const switchToSignup = () => {
    emit('switch-to-signup')
}
</script>

<style scoped>
.login-form {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
  padding: 0;
}


h2 {
  text-align: center;
  font-size: 20px;
  font-weight: 400;
  color: #333;
  margin: 0 0 24px 0;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
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

.login-btn {
  width: 100%;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  padding: 14px 20px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.login-btn:hover {
  background: linear-gradient(135deg, #20c997, #17a2b8);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.login-btn:active {
  transform: translateY(0);
}

.help-links {
  text-align: center;
  margin-bottom: 20px;
}

.help-link {
  display: block;
  color: #666;
  text-decoration: underline;
  font-size: 14px;
  margin-bottom: 8px;
  transition: color 0.2s ease;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.help-link:hover {
  color: #333;
}

.signup-link {
  text-align: center;
  font-size: 14px;
  color: #666;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.signup-link-btn {
  background: linear-gradient(135deg, #e91e63, #c2185b);
  border: none;
  color: white;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  padding: 8px 16px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
  margin-left: 8px;
  box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.signup-link-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
  background: linear-gradient(135deg, #c2185b, #ad1457);
}

.signup-link-btn:active {
  transform: translateY(0);
}

/* 機能案内セクション */
.features-section {
  margin-top: 16px;
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
