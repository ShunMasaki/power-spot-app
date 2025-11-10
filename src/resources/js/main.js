import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router/index.js'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

import { Amplify } from 'aws-amplify'
import awsconfig from './aws-exports'
import { fetchAuthSession } from 'aws-amplify/auth'

Amplify.configure(awsconfig)

// 環境変数からAPIのベースURLを取得（本番環境ではAPP_URLを使用）
axios.defaults.baseURL = import.meta.env.VITE_API_URL || window.location.origin

// リクエストインターセプターで認証トークンを自動追加
axios.interceptors.request.use(async (config) => {
  // APIリクエストのみトークンを追加（静的ファイルには不要）
  if (config.url && config.url.startsWith('/api/')) {
    try {
      const session = await fetchAuthSession({ forceRefresh: false })

      if (session?.tokens?.idToken) {
        const token = session.tokens.idToken.toString()
        config.headers.Authorization = `Bearer ${token}`
      } else if (session?.tokens?.accessToken) {
        // idTokenがない場合はaccessTokenを使用
        const token = session.tokens.accessToken.toString()
        config.headers.Authorization = `Bearer ${token}`
      } else {
        // トークンがない場合は強制リフレッシュを試す
        try {
          const refreshedSession = await fetchAuthSession({ forceRefresh: true })
          if (refreshedSession?.tokens?.idToken) {
            const token = refreshedSession.tokens.idToken.toString()
            config.headers.Authorization = `Bearer ${token}`
          } else if (refreshedSession?.tokens?.accessToken) {
            const token = refreshedSession.tokens.accessToken.toString()
            config.headers.Authorization = `Bearer ${token}`
          }
        } catch (refreshError) {
          // リフレッシュに失敗しても続行
        }
      }
    } catch (error) {
      // 認証セッションが見つからない場合はスキップ
    }
  }
  return config
})

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// 認証ストアを初期化
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
