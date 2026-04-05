<template>
  <footer id="contato" class="bg-emerald-50 text-emerald-900">
    <div class="max-w-6xl mx-auto px-6 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
        <div>
          <h3 class="font-serif font-bold text-lg">{{ siteTitle }}</h3>
          <p class="text-sm mt-4 text-emerald-800" v-html="aboutText"></p>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Contato</h4>
          <ul class="space-y-3 text-sm text-emerald-800">
            <li v-if="address" class="flex items-start gap-3">
              <span class="mt-1"><MapPin class="h-5 w-5 text-emerald-600" /></span>
              <span v-html="address"></span>
            </li>
            <li v-if="phone" class="flex items-center gap-3">
              <Phone class="h-5 w-5 text-emerald-600" />
              <a :href="'tel:' + phone.replace(/\D/g, '')" class="hover:underline">{{ phone }}</a>
            </li>
            <li v-if="email" class="flex items-center gap-3">
              <Mail class="h-5 w-5 text-emerald-600" />
              <a :href="'mailto:' + email" class="hover:underline">{{ email }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Redes Sociais</h4>
          <p class="text-sm text-emerald-800 mb-4">Siga-nos nas redes sociais para dicas de cuidados e novidades!</p>

          <div class="flex items-center gap-3">
            <a v-if="instagramUrl" :href="instagramUrl" target="_blank" rel="noopener" aria-label="Instagram" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
              <Instagram class="h-5 w-5" />
            </a>
            <a v-if="facebookUrl" :href="facebookUrl" target="_blank" rel="noopener" aria-label="Facebook" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
              <Facebook class="h-5 w-5" />
            </a>
          </div>
        </div>
      </div>

      <div class="border-t border-emerald-100 mt-8 pt-6">
        <div class="text-center text-xs text-emerald-700">&copy; {{ year }} {{ siteTitle }}. Todos os direitos reservados.</div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { MapPin, Phone, Mail, Instagram, Facebook } from 'lucide-vue-next'
import { fetchSettings } from '@/services/publicApi'

const year = new Date().getFullYear()
const siteTitle = ref('Clínica Duda')
const aboutText = ref('Cuidado especializado em podologia e estética, com dedicação e profissionalismo para o seu bem‑estar.')
const address = ref('Rua das Flores, 123 - Centro<br/>Joinville, SC - CEP 89201-000')
const phone = ref('(47) 99999-9999')
const email = ref('contato@clinicaduda.com.br')
const instagramUrl = ref<string | null>('#')
const facebookUrl = ref<string | null>('#')

onMounted(async () => {
  const settings = await fetchSettings()
  if (settings) {
    siteTitle.value = settings.site_title || siteTitle.value
    if (settings.about_text) aboutText.value = settings.about_text
    if (settings.address) address.value = settings.address
    if (settings.contact_phone) phone.value = settings.contact_phone
    if (settings.contact_email) email.value = settings.contact_email
    instagramUrl.value = settings.instagram_url || null
    facebookUrl.value = settings.facebook_url || null
  }
})
</script>

<style scoped></style>