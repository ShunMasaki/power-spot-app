<template>
  <div class="auth-example p-6 max-w-md mx-auto bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">認証テスト</h2>

    <!-- 認証状態表示 -->
    <div class="mb-6 p-4 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">認証状態</h3>
      <p>ログイン状態: {{ authStore.isLoggedIn ? 'ログイン中' : '未ログイン' }}</p>
      <p v-if="authStore.user">ユーザー: {{ authStore.username }}</p>
      <p v-if="authStore.idToken">トークン: {{ authStore.idToken.substring(0, 20) }}...</p>
    </div>

    <!-- サインアップ -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="font-semibold mb-3">サインアップ</h3>
      <div class="space-y-2">
        <input v-model="signUpForm.email" type="email" placeholder="メールアドレス" class="w-full p-2 border rounded">
        <input v-model="signUpForm.password" type="password" placeholder="パスワード" class="w-full p-2 border rounded">
        <input v-model="signUpForm.nickname" type="text" placeholder="ニックネーム" class="w-full p-2 border rounded">
        <button @click="handleSignUp" :disabled="signUpLoading" class="w-full p-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-400">
          {{ signUpLoading ? '処理中...' : 'サインアップ' }}
        </button>
      </div>
      <p v-if="signUpMessage" :class="signUpMessage.includes('成功') ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm">
        {{ signUpMessage }}
      </p>
    </div>

    <!-- サインアップ確認 -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="font-semibold mb-3">サインアップ確認</h3>
      <div class="space-y-2">
        <input v-model="confirmForm.email" type="email" placeholder="メールアドレス" class="w-full p-2 border rounded">
        <input v-model="confirmForm.code" type="text" placeholder="確認コード" class="w-full p-2 border rounded">
        <button @click="handleConfirmSignUp" :disabled="confirmLoading" class="w-full p-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:bg-gray-400">
          {{ confirmLoading ? '処理中...' : '確認' }}
        </button>
      </div>
      <p v-if="confirmMessage" :class="confirmMessage.includes('成功') ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm">
        {{ confirmMessage }}
      </p>
    </div>

    <!-- サインイン -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="font-semibold mb-3">サインイン</h3>
      <div class="space-y-2">
        <input v-model="signInForm.email" type="email" placeholder="メールアドレス" class="w-full p-2 border rounded">
        <input v-model="signInForm.password" type="password" placeholder="パスワード" class="w-full p-2 border rounded">
        <button @click="handleSignIn" :disabled="signInLoading" class="w-full p-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:bg-gray-400">
          {{ signInLoading ? '処理中...' : 'サインイン' }}
        </button>
      </div>
      <p v-if="signInMessage" :class="signInMessage.includes('成功') ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm">
        {{ signInMessage }}
      </p>
    </div>

    <!-- サインアウト -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="font-semibold mb-3">サインアウト</h3>
      <button @click="handleSignOut" :disabled="signOutLoading" class="w-full p-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:bg-gray-400">
        {{ signOutLoading ? '処理中...' : 'サインアウト' }}
      </button>
      <p v-if="signOutMessage" :class="signOutMessage.includes('成功') ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm">
        {{ signOutMessage }}
      </p>
    </div>

    <!-- トークン取得 -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="font-semibold mb-3">トークン取得</h3>
      <button @click="handleGetIdToken" :disabled="tokenLoading" class="w-full p-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 disabled:bg-gray-400">
        {{ tokenLoading ? '処理中...' : 'IDトークン取得' }}
      </button>
      <p v-if="tokenMessage" :class="tokenMessage.includes('成功') ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm">
        {{ tokenMessage }}
      </p>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import { ref, reactive } from 'vue'

export default {
  name: 'AuthExample',
  setup() {
    const authStore = useAuthStore()

    // フォームデータ
    const signUpForm = reactive({
      email: '',
      password: '',
      nickname: ''
    })

    const confirmForm = reactive({
      email: '',
      code: ''
    })

    const signInForm = reactive({
      email: '',
      password: ''
    })

    // ローディング状態
    const signUpLoading = ref(false)
    const confirmLoading = ref(false)
    const signInLoading = ref(false)
    const signOutLoading = ref(false)
    const tokenLoading = ref(false)

    // メッセージ
    const signUpMessage = ref('')
    const confirmMessage = ref('')
    const signInMessage = ref('')
    const signOutMessage = ref('')
    const tokenMessage = ref('')

    // サインアップ処理
    const handleSignUp = async () => {
      signUpLoading.value = true
      signUpMessage.value = ''

      const result = await authStore.signUp(
        signUpForm.email,
        signUpForm.password,
        signUpForm.nickname
      )

      if (result.success) {
        signUpMessage.value = 'サインアップが成功しました。確認コードを入力してください。'
        // 確認フォームにメールアドレスを自動入力
        confirmForm.email = signUpForm.email
      } else {
        signUpMessage.value = result.error
      }

      signUpLoading.value = false
    }

    // サインアップ確認処理
    const handleConfirmSignUp = async () => {
      confirmLoading.value = true
      confirmMessage.value = ''

      const result = await authStore.confirmSignUp(
        confirmForm.email,
        confirmForm.code
      )

      if (result.success) {
        confirmMessage.value = 'サインアップ確認が成功しました。サインインしてください。'
        // サインインフォームにメールアドレスを自動入力
        signInForm.email = confirmForm.email
      } else {
        confirmMessage.value = result.error
      }

      confirmLoading.value = false
    }

    // サインイン処理
    const handleSignIn = async () => {
      signInLoading.value = true
      signInMessage.value = ''

      const result = await authStore.signIn(
        signInForm.email,
        signInForm.password
      )

      if (result.success) {
        signInMessage.value = 'サインインが成功しました。'
      } else {
        signInMessage.value = result.error
      }

      signInLoading.value = false
    }

    // サインアウト処理
    const handleSignOut = async () => {
      signOutLoading.value = true
      signOutMessage.value = ''

      const result = await authStore.signOut()

      if (result.success) {
        signOutMessage.value = 'サインアウトが成功しました。'
      } else {
        signOutMessage.value = result.error
      }

      signOutLoading.value = false
    }

    // トークン取得処理
    const handleGetIdToken = async () => {
      tokenLoading.value = true
      tokenMessage.value = ''

      const result = await authStore.getIdToken()

      if (result.success) {
        tokenMessage.value = 'IDトークンの取得が成功しました。'
      } else {
        tokenMessage.value = result.error
      }

      tokenLoading.value = false
    }

    return {
      authStore,
      signUpForm,
      confirmForm,
      signInForm,
      signUpLoading,
      confirmLoading,
      signInLoading,
      signOutLoading,
      tokenLoading,
      signUpMessage,
      confirmMessage,
      signInMessage,
      signOutMessage,
      tokenMessage,
      handleSignUp,
      handleConfirmSignUp,
      handleSignIn,
      handleSignOut,
      handleGetIdToken
    }
  }
}
</script>
