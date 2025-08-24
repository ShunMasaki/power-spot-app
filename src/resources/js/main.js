import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

import { Amplify } from 'aws-amplify'
import awsconfig from './aws-exports'

Amplify.configure(awsconfig)

axios.defaults.baseURL = 'http://localhost:8080'

createApp(App).use(router).mount('#app')
