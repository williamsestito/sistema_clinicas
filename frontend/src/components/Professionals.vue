<template>
  <section id="profissionais" class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">{{ sectionTitle }}</h2>
      <p class="text-center text-sm text-emerald-700 mt-3">{{ sectionSubtitle }}</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 mt-12">
        <article
          v-for="(p, idx) in displayProfessionals"
          :key="p.name"
          :class="[
            'bg-white rounded-xl p-8 text-center border transition-transform duration-150 ease-in-out shadow-sm hover:-translate-y-1',
            idx === 0 ? 'border-emerald-200 ring-1 ring-emerald-50' : 'border-emerald-100'
          ]"
          role="article"
          aria-label="Profissional"
        >
          <div
            v-if="p.photo_url"
            class="mx-auto flex items-center justify-center rounded-full overflow-hidden w-20 h-20"
          >
            <img :src="p.photo_url" :alt="p.name" class="w-full h-full object-cover" />
          </div>
          <div
            v-else
            class="mx-auto flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600 font-semibold w-20 h-20"
            aria-hidden="true"
          >
            <span class="text-2xl">{{ p.initial }}</span>
          </div>

          <h3 class="mt-5 text-lg font-semibold text-emerald-900">{{ p.name }}</h3>

          <div class="mt-3 inline-block">
            <span class="text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full px-3 py-1">{{ p.role }}</span>
          </div>

          <p class="mt-4 text-sm text-emerald-600 leading-relaxed min-h-[64px]">{{ p.description }}</p>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { SiteSection, SiteProfessional } from '@/services/publicApi'

const props = defineProps<{
  section?: SiteSection
  professionals?: SiteProfessional[]
}>()

interface DisplayProfessional {
  name: string
  initial: string
  role: string
  description: string
  photo_url?: string | null
}

const sectionTitle = computed(() => props.section?.title || 'Nossos Profissionais')
const sectionSubtitle = computed(() => props.section?.content || 'Equipe qualificada e dedicada ao seu bem-estar')

const fallbackProfessionals: DisplayProfessional[] = [
  { name: 'Duda Silva', initial: 'D', role: 'Fundadora', description: 'Especialista em podologia com 10+ anos de experiência' },
  { name: 'Ana Martins', initial: 'A', role: 'Esteticista', description: 'Especialista em tratamentos faciais e corporais' },
  { name: 'Carla Santos', initial: 'C', role: 'Podóloga', description: 'Especializada em tratamentos podológicos avançados' },
  { name: 'Beatriz Lima', initial: 'B', role: 'Esteticista', description: 'Expert em design de sobrancelhas e micropigmentação' },
]

const displayProfessionals = computed<DisplayProfessional[]>(() => {
  if (props.professionals && props.professionals.length > 0) {
    return props.professionals.map(p => ({
      name: p.user?.name || 'Profissional',
      initial: (p.user?.name || 'P').charAt(0).toUpperCase(),
      role: p.specialty,
      description: p.bio || '',
      photo_url: p.photo_url,
    }))
  }
  return fallbackProfessionals
})
</script>

<style scoped>
section { padding-top: 5rem; padding-bottom: 5rem; }
article { min-height: 220px; display: flex; flex-direction: column; justify-content: flex-start; }
@media (min-width: 768px) {
  .w-20 { width: 84px; height: 84px; border-radius: 9999px; }
}
</style>