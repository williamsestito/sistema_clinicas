<template>
  <section id="servicos" class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">{{ sectionTitle }}</h2>
      <p class="text-center text-sm text-emerald-700 mt-2">{{ sectionSubtitle }}</p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
        <div
          v-for="(s, idx) in displayServices"
          :key="s.name"
          class="bg-white p-8 rounded-xl shadow-sm border border-emerald-100 transform transition-transform duration-150 hover:-translate-y-1"
        >
          <div class="mb-4">
            <div class="inline-flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-full p-3 w-10 h-10">
              <component :is="getIcon(idx)" class="h-5 w-5" />
            </div>
          </div>

          <h3 class="font-serif font-semibold text-emerald-900 text-lg">{{ s.name }}</h3>

          <p class="text-sm text-emerald-700 mt-4 leading-relaxed min-h-[72px]">{{ s.description }}</p>

          <a class="mt-6 inline-flex items-center text-sm text-emerald-600 hover:underline" href="#">
            <span>Saiba mais</span>
            <span class="ml-2">→</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Activity, Star, Heart, Eye, Zap, Sparkles } from 'lucide-vue-next'
import type { SiteSection, SiteService } from '@/services/publicApi'

const props = defineProps<{
  section?: SiteSection
  services?: SiteService[]
}>()

const icons = [Activity, Star, Heart, Zap, Eye, Sparkles]

function getIcon(idx: number) {
  return icons[idx % icons.length]
}

const sectionTitle = computed(() => props.section?.title || 'Nossos Serviços')
const sectionSubtitle = computed(() => props.section?.content || 'Tratamentos especializados para sua beleza e bem-estar')

const fallbackServices = [
  { name: 'Podologia Clínica', description: 'Tratamento especializado para saúde dos pés, unhas encravadas, calosidades e mais.' },
  { name: 'Spa dos Pés', description: 'Relaxamento profundo com hidratação, esfoliação e massagem terapêutica.' },
  { name: 'Limpeza de Pele', description: 'Cuidado facial completo com técnicas modernas para uma pele saudável e radiante.' },
  { name: 'Massagem Relaxante', description: 'Técnicas de massoterapia para aliviar tensões e promover bem‑estar.' },
  { name: 'Design de Sobrancelhas', description: 'Modelagem perfeita que valoriza seu olhar e harmoniza suas feições.' },
  { name: 'Tratamentos Corporais', description: 'Procedimentos estéticos avançados para cuidado completo do corpo.' },
]

const displayServices = computed(() => {
  if (props.services && props.services.length > 0) {
    return props.services
  }
  return fallbackServices
})
</script>

<style scoped>
/* ajustes visuais para ficar mais próximo da imagem */
section { padding-top: 5rem; padding-bottom: 5rem; }
@media (min-width: 768px) {
  .min-h-[72px] { min-height: 72px; }
}
</style>