import { defineStore } from 'pinia'
import { signIn, signOut, getCurrentUser } from 'aws-amplify/auth'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoggedIn: false,
        error: null,
    }),
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
                this.error = err.message || 'ログインに失敗しました。'
            }
        },
        async logout() {
            await signOut()
            this.isLoggedIn = false
            this.user = null
        },
    },
})
