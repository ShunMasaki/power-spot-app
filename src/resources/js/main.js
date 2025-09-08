import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

import { Amplify } from 'aws-amplify'
import awsconfig from './aws-exports'

Amplify.configure(awsconfig)

axios.defaults.baseURL = 'http://localhost:8080'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// 認証ストアを初期化
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
