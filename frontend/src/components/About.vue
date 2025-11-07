<template>
  <section id="sobre" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <!-- imagem à esquerda -->
        <div class="order-1 md:order-1">
          <div class="rounded-2xl overflow-hidden shadow-lg">
            <img :src="image" alt="Clínica Duda" class="w-full h-auto object-cover block" />
          </div>
        </div>

        <!-- texto à direita -->
        <div class="order-2 md:order-2">
          <h2 class="text-3xl md:text-4xl font-serif font-bold text-emerald-900 mb-4">Sobre a Clínica Duda</h2>

          <p class="text-sm text-emerald-700 mb-4">
            Com anos de experiência em podologia e estética, a Clínica Duda nasceu do sonho de oferecer um espaço acolhedor onde beleza e saúde caminham juntas.
          </p>

          <p class="text-sm text-emerald-700 mb-4">
            Fundada por Duda, profissional dedicada e apaixonada pelo bem‑estar de seus clientes, nossa clínica se tornou referência em Joinville pela excelência no atendimento e pela qualidade dos tratamentos oferecidos.
          </p>

          <p class="text-sm text-emerald-700 mb-4">
            Acreditamos que cuidar de si mesmo é um ato de amor próprio. Por isso combinamos técnicas modernas com um atendimento humanizado, criando uma experiência única e transformadora.
          </p>

          <p class="text-sm text-emerald-600 font-semibold">Seu bem‑estar é nossa prioridade.</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const props = defineProps({
  image: { type: String, default: '/about.jpg' }
})
</script>

<style scoped>
/* pequenas correções para garantir aspecto igual ao mock */
section { padding-top: 5rem; padding-bottom: 5rem; }
@media (min-width: 768px) {
  img { height: 420px; object-fit: cover; }
}
</style>