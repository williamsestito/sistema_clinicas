<template>
  <section class="py-16 bg-emerald-50">
    <div class="max-w-4xl mx-auto px-6 text-center">
      <div class="flex flex-col items-center gap-4">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600">
          <Calendar class="w-6 h-6" />
        </div>

        <h3 class="text-3xl md:text-4xl font-serif font-bold text-emerald-900">{{ ctaTitle }}</h3>
        <p class="text-sm text-emerald-700 max-w-2xl" v-html="ctaContent">
        </p>

        <a
          href="#agendamento"
          class="mt-3 inline-flex items-center gap-3 bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-full text-sm font-medium shadow"
        >
          <Calendar class="w-4 h-4" /> Agendar agora
        </a>

        <ul class="mt-6 flex flex-col sm:flex-row gap-3 text-xs text-emerald-600 items-center">
          <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Agendamento online</li>
          <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Profissionais qualificados</li>
          <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Ambiente acolhedor</li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Calendar } from 'lucide-vue-next'
import type { SiteSection } from '@/services/publicApi'

const props = defineProps<{
  section?: SiteSection
}>()

const ctaTitle = computed(() => props.section?.title || 'Pronta para se cuidar?')
const ctaContent = computed(() => props.section?.content || 'Agende seu horário online com nossos profissionais e dê o primeiro passo rumo ao seu bem‑estar.')
</script>

<style scoped>
section { padding-top: 4rem; padding-bottom: 4rem; }
</style>