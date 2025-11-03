<template>
  <div>
    <Hero />

    <main>
      <section id="sobre" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-6 mt-12 md:mt-16 lg:mt-20">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
              <div class="overflow-hidden rounded-2xl shadow-lg">
                <img src="/assets/about.jpg" alt="Sobre" class="w-full h-full object-cover block" />
              </div>
            </div>

            <div>
              <h2 class="text-3xl md:text-4xl font-serif font-bold text-emerald-900">Sobre a Clínica Duda</h2>
              <p class="mt-6 text-emerald-700 leading-relaxed">Com anos de experiência em podologia e estética, a Clínica Duda nasceu do sonho de oferecer um espaço acolhedor onde beleza e saúde caminham juntas.</p>
              <p class="mt-4 text-emerald-700 leading-relaxed">Fundada por Duda, profissional dedicada e apaixonada pelo bem-estar de seus clientes, nossa clínica se tornou referência em Joinville pela excelência no atendimento e pela qualidade dos tratamentos oferecidos.</p>
              <p class="mt-4 text-emerald-700 leading-relaxed">Acreditamos que cuidar de si mesmo é um ato de amor próprio. Por isso, combinamos técnicas modernas com um atendimento humanizado, criando uma experiência única e transformadora para cada cliente.</p>
              <p class="mt-4 font-semibold text-emerald-600">Seu bem-estar é nossa prioridade.</p>
            </div>
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

<script setup lang="ts">
import Hero from '@/components/Hero.vue'
import ServicesGrid from '@/components/ServicesGrid.vue'
import Professionals from '@/components/Professionals.vue'
import Testimonials from '@/components/Testimonials.vue'
</script>

<style scoped></style>