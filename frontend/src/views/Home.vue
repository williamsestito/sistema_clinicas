<template>
  <div v-if="loading" class="min-h-screen flex items-center justify-center bg-emerald-50">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
  </div>
  <template v-else>
    <Hero v-if="sectionMap.hero" :section="sectionMap.hero" />
    <About v-if="sectionMap.sobre" :section="sectionMap.sobre" image="/images/clinica-sala.jpg" />
    <ServicesGrid v-if="sectionMap.servicos" :section="sectionMap.servicos" :services="services" />
    <Professionals v-if="sectionMap.profissionais" :section="sectionMap.profissionais" :professionals="professionals" />
    <Testimonials v-if="testimonials.length" :testimonials="testimonials" />
    <CallToAction v-if="sectionMap.cta" :section="sectionMap.cta" />
  </template>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import Hero from '@/components/Hero.vue'
import About from '@/components/About.vue'
import ServicesGrid from '@/components/ServicesGrid.vue'
import Professionals from '@/components/Professionals.vue'
import Testimonials from '@/components/Testimonials.vue'
import CallToAction from '@/components/CallToAction.vue'
import {
  fetchSections,
  fetchSettings,
  fetchTestimonials,
  fetchServices,
  fetchProfessionals,
  type SiteSection,
  type SiteSettings,
  type SiteTestimonial,
  type SiteService,
  type SiteProfessional,
} from '@/services/publicApi'

const loading = ref(true)
const sections = ref<SiteSection[]>([])
const settings = ref<SiteSettings | null>(null)
const testimonials = ref<SiteTestimonial[]>([])
const services = ref<SiteService[]>([])
const professionals = ref<SiteProfessional[]>([])

const sectionMap = computed(() => {
  const map: Record<string, SiteSection> = {}
  for (const s of sections.value) {
    map[s.slug] = s
  }
  return map
})

onMounted(async () => {
  try {
    const [sec, set, test, srv, prof] = await Promise.all([
      fetchSections(),
      fetchSettings(),
      fetchTestimonials(),
      fetchServices(),
      fetchProfessionals(),
    ])
    sections.value = sec
    settings.value = set
    testimonials.value = test
    services.value = srv
    professionals.value = prof
  } catch (e) {
    console.error('Erro ao carregar dados do site:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped></style>