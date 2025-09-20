<template>
    <div>
        <!-- ヘッダー（白） -->
        <header class="header-bar">
            <router-link to="/" class="logo-link">
                <img
                src="./assets/logo/logo.png"
                alt="パワスポ！ロゴ"
                class="logo"
                />
            </router-link>

            <!-- 右側のボタン -->
            <div class="header-right">
                <button v-if="!auth.isLoggedIn" @click="auth.openLoginModal" class="auth-btn login-btn">
                    ログイン
                </button>
                <button v-if="!auth.isLoggedIn" @click="auth.openSignupModal" class="auth-btn signup-btn">
                    新規登録
                </button>
                <button v-if="auth.isLoggedIn" @click="goToMyPage" class="auth-btn mypage-btn">
                    マイページ
                </button>
                <button v-if="auth.isLoggedIn" @click="auth.logout" class="auth-btn logout-btn">
                    ログアウト
                </button>
            </div>
        </header>

        <!-- 各ページを Vue Router で切り替え -->
        <router-view />

    </div>

    <LoginModal
        :isOpen="auth.loginModalOpen"
        @close="auth.closeModals"
        @login-success="handleLoginSuccess"
        @switch-to-signup="auth.openSignupModal"
    />
    <SignupModal
        :isOpen="auth.signupModalOpen"
        @close="auth.closeModals"
        @signup-success="auth.closeModals"
        @switch-to-login="auth.openLoginModal"
    />
</template>

<script setup>
import { ref, watch, provide } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginModal from './components/LoginModal.vue'
import SignupModal from './components/SignupModal.vue'

const auth = useAuthStore()
const route = useRoute()

// ログイン成功時のコールバックを管理
const loginSuccessCallback = ref(null)

// ルート変更時にモーダルを閉じる
watch(() => route.path, () => {
    auth.closeModals()
    loginSuccessCallback.value = null
    // エラーメッセージもクリア
    auth.error = null
})


const handleLoginSuccess = () => {
    auth.closeModals()
    // コールバックが設定されている場合は実行
    if (loginSuccessCallback.value) {
        loginSuccessCallback.value()
        loginSuccessCallback.value = null
    }
}

const goToMyPage = () => {
    // マイページへの遷移（今後作成予定）
    console.log('マイページへ遷移予定')
    // router.push('/mypage') // マイページ作成後に有効化
}

// ログイン成功時のコールバックを設定する関数をprovide
provide('setLoginSuccessCallback', (callback) => {
    loginSuccessCallback.value = callback
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ヘッダー */
.header-bar {
background: #ffffff;
position: fixed;
top: 0;
left: 0;
width: 100%;
z-index: 200;
border-bottom: 1px solid rgba(0,0,0,0.06);
padding: 0px 80px;
display: flex;
align-items: center;
justify-content: space-between;
height: auto;
margin: 0;
box-sizing: border-box;
}

.logo-link { text-decoration: none; display: inline-block; }
.logo { height: 50px; width: auto; display: block; margin: 0; padding: 0; vertical-align: top; }

/* ヘッダー右側 */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-right: 120px;
}

.auth-btn {
  border: none;
  padding: 4px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 400;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: all 0.3s ease;
  letter-spacing: -0.01em;
  min-width: 70px;
}

.login-btn {
  background: transparent;
  color: #e91e63;
  border: 1px solid #e91e63;
}

.login-btn:hover {
  background: #e91e63;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3);
}

.signup-btn {
  background: #e91e63;
  color: white;
}

.signup-btn:hover {
  background: #c2185b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

.mypage-btn {
  background: #e91e63;
  color: white;
}

.mypage-btn:hover {
  background: #c2185b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

.logout-btn {
  background: #e91e63;
  color: white;
}

.logout-btn:hover {
  background: #c2185b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}
</style>
