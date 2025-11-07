import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

// Simulando que vem do banco de dados
const siteTitle = 'Clínica Duda - Podologia'
document.title = siteTitle

app.use(createPinia())
app.use(router)

app.mount('#app')
