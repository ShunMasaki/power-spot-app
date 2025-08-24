<template>
    <div>
        <h1>ログインページ</h1>

        <div v-if="!user">
            <button @click="login">ログイン</button>
        </div>

        <div v-else>
            <p>こんにちは、{{ user.username }} さん</p>
            <button @click="logout">ログアウト</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCurrentUser, signOut, signInWithRedirect } from 'aws-amplify/auth'

const user = ref(null)

const login = async () => {
    try {
        await signInWithRedirect()
    } catch (error) {
        console.error('ログインエラー:', error)
    }
}

const logout = async () => {
    try {
        await signOut({ global: true })
        user.value = null
        window.location.reload()
    } catch (error) {
        console.error('ログアウトエラー:', error)
    }
}

onMounted(async () => {
    try {
        const current = await getCurrentUser()
        user.value = current
    } catch {
        user.value = null
    }
})
</script>
