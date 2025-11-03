<template>
  <section id="top" class="relative h-screen flex items-center" style="background: linear-gradient(90deg, rgba(255,255,255,0.0), rgba(0,0,0,0.0));">
    <div class="absolute inset-0 bg-[url('/assets/hero.jpg')] bg-cover bg-center -z-10"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/40 to-transparent -z-5"></div>

    <div class="max-w-6xl mx-auto px-6 text-white">
      <div class="pt-24 md:pt-36 lg:pt-44 max-w-3xl">
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-extrabold leading-tight drop-shadow-lg">Beleza e bem-estar<br/>começam pelos seus pés.</h1>
        <p class="mt-6 max-w-2xl text-lg text-white/90">Cuidado, saúde e estética em um só lugar.</p>

        <div class="mt-6">
          <a href="#agendamento" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white rounded-full px-6 py-3 shadow-lg">Agende seu horário</a>
        </div>
      </div>
    </div>

    <!-- scroll indicator -->
  <div class="absolute bottom-16 left-1/2 transform -translate-x-1/2">
      <button @click.prevent="document.querySelector('#sobre')?.scrollIntoView({ behavior: 'smooth' })" class="scroll-indicator flex items-center justify-center h-10 w-10 rounded-full bg-white/20 text-white hover:bg-white/30">
        <!-- mouse icon: rounded rect with wheel line -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="7" y="3" width="10" height="14" rx="5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          <path d="M12 7v3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
      </button>
    </div>
  </section>
</template>

<script setup lang="ts"></script>

<style scoped>
section { min-height: 680px; }

/* scroll indicator animation */
.scroll-indicator { animation: scroll-pulse 1.8s ease-in-out infinite; }
@keyframes scroll-pulse {
  0% { transform: translateY(0); }
  50% { transform: translateY(6px); }
  100% { transform: translateY(0); }
}
</style>