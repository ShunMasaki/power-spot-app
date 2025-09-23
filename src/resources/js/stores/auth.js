import { defineStore } from 'pinia'
import { signIn, signOut, getCurrentUser } from 'aws-amplify/auth'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoggedIn: false,
        error: null,
        loginModalOpen: false,
        signupModalOpen: false,
    }),
    getters: {
        isAuthenticated: (state) => state.isLoggedIn,
    },
    actions: {
        async initializeAuth() {
            try {
                const user = await getCurrentUser()
                this.user = user
                this.isLoggedIn = true
            } catch {
                this.user = null
                this.isLoggedIn = false
            }
        },
        async login(email, password) {
            try {
                const { isSignedIn, nextStep } = await signIn({
                    username: email,
                    password: password,
                })
                if (isSignedIn) {
                    this.isLoggedIn = true
                    this.user = { email }
                    this.error = null
                } else {
                    this.error = `追加ステップが必要: ${nextStep.signInStep}`
                }
            } catch (error) {
                this.error = this.translateError(error)
            }
        },
        async logout() {
            await signOut()
            this.isLoggedIn = false
            this.user = null
        },
        openLoginModal() {
            this.signupModalOpen = false
            this.loginModalOpen = true
            this.error = null  // エラーメッセージをクリア
        },
        openSignupModal() {
            this.loginModalOpen = false
            this.signupModalOpen = true
            this.error = null  // エラーメッセージをクリア
        },
        closeModals() {
            this.loginModalOpen = false
            this.signupModalOpen = false
            this.error = null  // エラーメッセージをクリア
        },
        translateError(error) {
            const errorMessage = error.message || error.toString()

            // よくあるエラーメッセージの日本語化
            if (errorMessage.includes('Incorrect username or password') || errorMessage.includes('User does not exist')) {
                return 'メールアドレスまたはパスワードが正しくありません。'
            }
            if (errorMessage.includes('User is not confirmed')) {
                return 'アカウントが確認されていません。メールを確認してください。'
            }
            if (errorMessage.includes('Password attempts exceeded')) {
                return 'パスワードの試行回数が上限に達しました。しばらくしてから再試行してください。'
            }
            if (errorMessage.includes('Invalid password format')) {
                return 'パスワードの形式が正しくありません。'
            }
            if (errorMessage.includes('Network error') || errorMessage.includes('fetch')) {
                return 'ネットワークエラーが発生しました。接続を確認してください。'
            }

            // その他のエラーは元のメッセージを返す
            return errorMessage || 'ログインに失敗しました。'
        },
    },
})
