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

// 認証ストアをインポート
import { useAuthStore } from './stores/auth'

try {
  const app = createApp(App)
  const pinia = createPinia()

  app.use(pinia)
  app.use(router)

  // 認証ストアを初期化
  const authStore = useAuthStore()

  // エラーハンドリング付きで初期化
  authStore.initializeAuth().catch(error => {
    console.error('Auth store initialization error:', error)
  })

  app.mount('#app')
} catch (error) {
  console.error('Failed to mount app:', error)
  // エラーを画面に表示
  const appElement = document.getElementById('app')
  if (appElement) {
    appElement.innerHTML = `
      <div style="padding: 20px; text-align: center;">
        <h1>エラーが発生しました</h1>
        <p>アプリの読み込みに失敗しました。ページを再読み込みしてください。</p>
        <p style="color: red; font-size: 12px; margin-top: 20px;">${error.message}</p>
      </div>
    `
  }
}
