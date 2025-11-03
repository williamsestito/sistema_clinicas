<script setup lang="ts">
import Hero from '@/components/Hero.vue'
import ServicesGrid from '@/components/ServicesGrid.vue'
import Professionals from '@/components/Professionals.vue'
import Testimonials from '@/components/Testimonials.vue'
</script>

<template>
  <div>
    <Hero />

    <main class="-mt-16">
      <section id="sobre" class="max-w-6xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div>
            <img src="/assets/about.jpg" alt="Sobre" class="rounded-lg shadow-md" />
          </div>
          <div>
            <h2 class="text-2xl font-serif font-bold text-emerald-900">Sobre a Clínica Duda</h2>
            <p class="mt-4 text-emerald-700">Com anos de experiência em podologia e estética, a Clínica Duda nasceu do sonho de oferecer um ambiente acolhedor.</p>
            <a href="#" class="mt-6 inline-block bg-emerald-500 text-white px-4 py-2 rounded">Seu bem-estar é nossa prioridade</a>
          </div>
        </div>
      </section>

      <ServicesGrid />
      <Professionals />
      <Testimonials />

      <section class="py-16 bg-emerald-50">
        <div class="max-w-6xl mx-auto px-6 text-center">
          <h3 class="text-xl font-semibold">Pronta para se cuidar?</h3>
          <p class="mt-2 text-emerald-700">Agende seu horário online com nossos profissionais.</p>
          <button class="mt-6 bg-emerald-500 text-white px-6 py-3 rounded">Agendar agora</button>
        </div>
      </section>
    </main>
  </div>
</template>
