<template>
  <section id="profissionais" class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">Nossos Profissionais</h2>
      <p class="text-center text-sm text-emerald-700 mt-3">Equipe qualificada e dedicada ao seu bem-estar</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 mt-12">
        <article
          v-for="(p, idx) in professionals"
          :key="p.name"
          :class="[
            'bg-white rounded-xl p-8 text-center border transition-transform duration-150 ease-in-out shadow-sm hover:-translate-y-1',
            idx === 0 ? 'border-emerald-200 ring-1 ring-emerald-50' : 'border-emerald-100'
          ]"
          role="article"
          aria-label="Profissional"
        >
          <div
            class="mx-auto flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600 font-semibold"
            :class="['w-20 h-20']"
            aria-hidden="true"
          >
            <span class="text-2xl">{{ p.initial }}</span>
          </div>

          <h3 class="mt-5 text-lg font-semibold text-emerald-900">{{ p.name }}</h3>

          <div class="mt-3 inline-block">
            <span class="text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full px-3 py-1">{{ p.role }}</span>
          </div>

          <p class="mt-4 text-sm text-emerald-600 leading-relaxed min-h-[64px]">{{ p.description }}</p>

          <div v-if="p.title" class="mt-3 text-xs text-emerald-500">{{ p.title }}</div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
type Professional = {
  name: string
  initial: string
  role: string
  title?: string
  description: string
}

const professionals: Professional[] = [
  {
    name: 'Duda Silva',
    initial: 'D',
    role: 'Fundadora',
    title: '',
    description: 'Especialista em podologia com 10+ anos de experiência'
  },
  {
    name: 'Ana Martins',
    initial: 'A',
    role: 'Esteticista',
    title: '',
    description: 'Especialista em tratamentos faciais e corporais'
  },
  {
    name: 'Carla Santos',
    initial: 'C',
    role: 'Podóloga',
    title: '',
    description: 'Especializada em tratamentos podológicos avançados'
  },
  {
    name: 'Beatriz Lima',
    initial: 'B',
    role: 'Esteticista',
    title: '',
    description: 'Expert em design de sobrancelhas e micropigmentação'
  }
]
</script>

<style scoped>
section { padding-top: 5rem; padding-bottom: 5rem; }
article { min-height: 220px; display: flex; flex-direction: column; justify-content: flex-start; }
@media (min-width: 768px) {
  .w-20 { width: 84px; height: 84px; border-radius: 9999px; }
}
</style>