<template>
    <div>
        <!-- ロゴ：クリックでトップに戻る -->
        <router-link to="/" class="logo-link">
            <img
            src="./assets/logo/pawaspo_logo.svg"
            alt="パワスポ！ロゴ"
            class="logo-fixed"
            />
        </router-link>

        <!-- 各ページを Vue Router で切り替え -->
        <router-view />

        <div class="fixed bottom-4 right-4">
            <template v-if="auth.isLoggedIn">
                <button
                    @click="auth.logout"
                    class="bg-red-500 text-white px-4 py-2 rounded shadow"
                >
                    ログアウト
                </button>
            </template>
            <template v-else>
                <div class="flex gap-2">
                    <button @click="openLogin" class="bg-gray-700 text-white px-4 py-2 rounded shadow">ログイン</button>
                    <button @click="openSignup" class="bg-blue-600 text-white px-4 py-2 rounded shadow">新規登録</button>
                </div>
            </template>
        </div>
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

const openLogin = () => {
    auth.openLoginModal()
}
const openSignup = () => {
    auth.openSignupModal()
}

const handleLoginSuccess = () => {
    auth.closeModals()
    // コールバックが設定されている場合は実行
    if (loginSuccessCallback.value) {
        loginSuccessCallback.value()
        loginSuccessCallback.value = null
    }
}

// ログイン成功時のコールバックを設定する関数をprovide
provide('setLoginSuccessCallback', (callback) => {
    loginSuccessCallback.value = callback
})
</script>

<style>
body {
font-family: 'Noto Sans JP', 'Noto Sans', 'Helvetica Neue', Arial, 'Meiryo', sans-serif;
}
.logo-fixed {
position: fixed;
top: 18px;
left: 18px;
width: 60px;
z-index: 100;
}
</style>
