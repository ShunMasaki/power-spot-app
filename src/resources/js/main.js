import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

import { Amplify } from 'aws-amplify'
import awsconfig from './aws-exports'
import { fetchAuthSession } from 'aws-amplify/auth'

Amplify.configure(awsconfig)

axios.defaults.baseURL = 'http://localhost:8080'

// リクエストインターセプターで認証トークンを自動追加
axios.interceptors.request.use(async (config) => {
  try {
    const session = await fetchAuthSession()
    if (session.tokens?.idToken) {
      config.headers.Authorization = `Bearer ${session.tokens.idToken.toString()}`
    }
  } catch (error) {
    // 認証セッションが見つからない場合はスキップ
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
