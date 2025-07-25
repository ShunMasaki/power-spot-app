import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

createApp(App)
.use(router)
.mount('#app')

axios.defaults.baseURL = 'http://localhost:8080'
