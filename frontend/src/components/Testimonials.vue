<template>
  <section class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">O Que Dizem Nossos Clientes</h2>
      <p class="text-center text-sm text-emerald-700 mt-2">Depoimentos reais de quem confia em nossos serviços</p>

      <!-- carousel -->
      <div class="relative mt-8">
        <div class="overflow-hidden" ref="viewportRef">
          <div
            ref="trackRef"
            class="flex items-stretch transition-transform duration-500"
            :style="{ transform: `translateX(${-currentIndex * slideWidth}px)` }"
            @transitionend="onTransitionEnd"
          >
            <article
              v-for="(t, idx) in slides"
              :key="idx + '-' + t.author"
              class="bg-white p-6 md:p-8 rounded-xl border border-emerald-100 shadow-sm transform transition-all duration-150 mx-3 flex flex-col justify-between"
              :style="{ minWidth: slideWidth + 'px' }"
              role="blockquote"
              aria-label="Depoimento"
            >
              <div class="flex items-center gap-1 mb-4" aria-hidden="true">
                <span v-for="n in 5" :key="n" class="text-emerald-500 text-[16px] leading-none">★</span>
              </div>

              <p class="text-emerald-700 text-sm italic leading-relaxed min-h-[120px]">“{{ t.quote }}”</p>

              <footer class="mt-6 text-sm">
                <div class="text-emerald-900 font-semibold">— {{ t.author }}</div>
              </footer>
            </article>
          </div>
        </div>

        <!-- controls -->
        <button
          @click="prev"
          aria-label="Anterior"
          class="hidden md:flex items-center justify-center h-10 w-10 rounded-full bg-white/80 border shadow ml-2 absolute left-0 top-1/2 -translate-y-1/2"
        >
          ‹
        </button>
        <button
          @click="next"
          aria-label="Próximo"
          class="hidden md:flex items-center justify-center h-10 w-10 rounded-full bg-white/80 border shadow mr-2 absolute right-0 top-1/2 -translate-y-1/2"
        >
          ›
        </button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'

type Testimonial = {
  quote: string
  author: string
}

const testimonials: Testimonial[] = [
  {
    quote:
      'Atendimento excepcional! A Duda é extremamente profissional e atenciosa. Meus pés nunca estiveram tão bem cuidados.',
    author: 'Maria Silva'
  },
  {
    quote:
      'Ambiente acolhedor e serviços de primeira qualidade. Recomendo a todos que buscam cuidados especializados.',
    author: 'João Santos'
  },
  {
    quote:
      'A limpeza de pele foi maravilhosa! Profissionais competentes e produtos de excelente qualidade.',
    author: 'Ana Paula'
  },
  {
    quote:
      'Tratamento podológico impecável. Senti alívio imediato após a primeira sessão. Equipe nota 10!',
    author: 'Carlos Mendes'
  }
  // ... mais depoimentos podem ser adicionados aqui
]

// --- carousel logic (infinite) ---
const viewportRef = ref<HTMLElement | null>(null)
const trackRef = ref<HTMLElement | null>(null)

const visibleCount = ref(4) // será atualizado conforme tamanho da tela
const slideWidth = ref(0)
const isTransitioning = ref(false)

// criamos clones: [last, ...originals, first] para loop infinito simples
const slides = computed(() => {
  if (testimonials.length === 0) return []
  const arr = [...testimonials]
  const first = arr[0]
  const last = arr[arr.length - 1]
  return [last, ...arr, first]
})

const currentIndex = ref(1) // começa no primeiro "real" (índice 1 no array com clones)

let resizeObserver: ResizeObserver | null = null
let autoplayTimer: number | null = null

function updateVisibleCountByWidth() {
  const w = window.innerWidth
  if (w >= 1024) visibleCount.value = 4
  else if (w >= 640) visibleCount.value = 2
  else visibleCount.value = 1
}

async function updateSlideWidth() {
  await nextTick()
  const vp = viewportRef.value
  if (!vp) return
  slideWidth.value = Math.floor(vp.clientWidth / visibleCount.value)
}

function onTransitionEnd() {
  isTransitioning.value = false
  // se estivermos no clone final ou inicial, saltar sem transição
  if (currentIndex.value === 0) {
    // saltou para o clone do último - pular para o real último
    currentIndex.value = slides.value.length - 2
    disableTransitionTemporarily()
  } else if (currentIndex.value === slides.value.length - 1) {
    // saltou para o clone do primeiro - pular para o real primeiro
    currentIndex.value = 1
    disableTransitionTemporarily()
  }
}

function disableTransitionTemporarily() {
  const track = trackRef.value
  if (!track) return
  track.style.transition = 'none'
  // forçar reflow para aplicar
  void track.offsetHeight
  track.style.transition = ''
}

function next() {
  if (isTransitioning.value) return
  isTransitioning.value = true
  currentIndex.value++
}

function prev() {
  if (isTransitioning.value) return
  isTransitioning.value = true
  currentIndex.value--
}

function startAutoplay() {
  stopAutoplay()
  autoplayTimer = window.setInterval(() => {
    next()
  }, 3500)
}
function stopAutoplay() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

onMounted(() => {
  updateVisibleCountByWidth()
  updateSlideWidth()
  window.addEventListener('resize', () => {
    updateVisibleCountByWidth()
    updateSlideWidth()
  })

  // garantir posição inicial (no primeiro slide real)
  // currentIndex já começa em 1, mas recalcula slideWidth antes de posicionar
  nextTick(() => {
    updateSlideWidth()
  })

  // autoplay
  startAutoplay()

  // pausar autoplay ao passar o mouse
  const vp = viewportRef.value
  if (vp) {
    vp.addEventListener('mouseenter', stopAutoplay)
    vp.addEventListener('mouseleave', startAutoplay)
  }
})

onUnmounted(() => {
  stopAutoplay()
  window.removeEventListener('resize', updateSlideWidth)
})
</script>

<style scoped>
section { padding-top: 5rem; padding-bottom: 5rem; }

/* estilo do card e espaçamento para parecer com a imagem */
article { display: flex; flex-direction: column; justify-content: space-between; }
.min-h-\[120px\] { min-height: 120px; }

/* esconder barra de rolagem no track se aparecer */
div[ref="trackRef"]::-webkit-scrollbar { display: none; }
</style>