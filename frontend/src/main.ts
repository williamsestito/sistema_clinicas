import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

// Temporary site title (simulate value coming from backend)
const siteTitle = 'clinica duda - podologia'
document.title = siteTitle

app.use(createPinia())
app.use(router)

app.mount('#app')
