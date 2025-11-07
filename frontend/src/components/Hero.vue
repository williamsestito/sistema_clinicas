<template>
  <section
    class="relative h-[86vh] min-h-[560px] flex items-center"
    :style="heroStyle"
    aria-label="Hero - Beleza e bem-estar"
  >
    <!-- overlay escuro com leve tonalidade esmeralda -->
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/35 via-transparent to-transparent"></div>

    <div class="max-w-6xl mx-auto px-6 w-full z-10">
      <div class="flex items-start">
        <!-- conteúdo principal -->
        <div class="w-full lg:w-full pt-16 md:pt-24">
          <h1 class="font-serif text-4xl md:text-6xl lg:text-7xl text-white leading-tight drop-shadow-sm">
            Beleza e bem-estar<br />
            começam pelos seus<br />
            pés.
          </h1>

          <p class="mt-6 text-emerald-100/90 text-sm md:text-base max-w-xl">
            Cuidado, saúde e estética em um só lugar.
          </p>

          <div class="mt-8">
            <a
              href="#agendamento"
              class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-full text-sm font-medium shadow-md transition"
              aria-label="Agende seu horário"
            >
              Agende seu horário
            </a>
          </div>
        </div>

        <!-- espaço/efeito decorativo à direita (opcional) -->
        <div class="hidden lg:block flex-1"></div>
      </div>
    </div>

    <!-- indicador de scroll -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20">
      <a href="#sobre" class="flex items-center justify-center w-10 h-16 rounded-full border border-white/30 text-white/80">
        <span class="block w-2 h-2 rounded-full bg-white/80 animate-bounce"></span>
      </a>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useHead } from '@vueuse/head' // opcional, remova se não usar

// aceita prop com URL da imagem (facilita trocar depois)
const props = defineProps({
  image: {
    type: String,
    default: '/hero.jpg' // substitua pelo caminho da imagem que você for colocar
  }
})

const heroStyle = computed(() => {
  return {
    backgroundImage: `url('${props.image}')`,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat'
  }
})
</script>

<style scoped>
section {
  /* garante que a imagem preencha e o overlay funcione bem */
  position: relative;
  overflow: hidden;
}

/* leve escurecimento local para garantir contraste do texto em imagens claras */
section::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(6,78,59,0.15) 0%, rgba(6,78,59,0.28) 60%, rgba(6,78,59,0.45) 100%);
  pointer-events: none;
  z-index: 5;
}
</style>