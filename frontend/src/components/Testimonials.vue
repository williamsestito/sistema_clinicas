<template>
  <section class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">O Que Dizem Nossos Clientes</h2>
      <p class="text-center text-sm text-emerald-700 mt-2">Depoimentos reais de quem confia em nossos serviços</p>

      <!-- carousel: scrollable, drag-to-scroll, sem autoplay -->
      <div class="relative mt-8">
        <!-- viewport: permite scroll horizontal por arrastar / swipe -->
        <div
          ref="viewportRef"
          class="overflow-x-auto no-scrollbar"
          @pointerdown="onPointerDown"
          @pointerup="onPointerUp"
          @pointercancel="onPointerUp"
          @pointerleave="onPointerUp"
          @pointermove="onPointerMove"
        >
          <div
            ref="trackRef"
            class="flex items-stretch gap-6 px-2"
            :style="{ paddingBottom: '4px' }"
          >
            <article
              v-for="(t, idx) in testimonials"
              :key="idx + '-' + t.author"
              class="bg-white p-6 md:p-8 rounded-xl border border-emerald-100 shadow-sm flex-shrink-0"
              :class="cardWidthClass"
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

        <!-- controls: visíveis e com área de clique maior para acessibilidade -->
        <button
          @click="scrollPrev"
          aria-label="Anterior"
          class="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white p-2 rounded-full shadow-md border"
        >
          ‹
        </button>

        <button
          @click="scrollNext"
          aria-label="Próximo"
          class="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white p-2 rounded-full shadow-md border"
        >
          ›
        </button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'

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
  // ... adicione mais depoimentos aqui
]

// refs
const viewportRef = ref<HTMLElement | null>(null)
const trackRef = ref<HTMLElement | null>(null)

// estado de responsividade
const visibleCount = ref(4)

// card width css class (computed para responsividade)
const cardWidthClass = computed(() => {
  // usa classes tailwind-like para largura fixa por breakpoints
  // em mobile 90% largura, sm: 45% (2 col), lg: 23% (4 col)
  return 'scroll-card w-[88%] sm:w-[45%] lg:w-[23%] md:w-[30%]'
})

// drag state
let isPointerDown = false
let startX = 0
let startScroll = 0
let pointerId: number | null = null

function updateVisibleCountByWidth() {
  const w = window.innerWidth
  if (w >= 1024) visibleCount.value = 4
  else if (w >= 640) visibleCount.value = 2
  else visibleCount.value = 1
}

async function recalc() {
  updateVisibleCountByWidth()
  await nextTick()
  // opcional: poderia calcular tamanhos se necessário
}

function scrollNext() {
  const vp = viewportRef.value
  if (!vp) return
  const amount = Math.floor(vp.clientWidth * 0.9) // rola quase a viewport
  vp.scrollBy({ left: amount, behavior: 'smooth' })
}

function scrollPrev() {
  const vp = viewportRef.value
  if (!vp) return
  const amount = Math.floor(vp.clientWidth * 0.9)
  vp.scrollBy({ left: -amount, behavior: 'smooth' })
}

// pointer / drag handlers (funciona mouse + touch + pen)
function onPointerDown(e: PointerEvent) {
  const vp = viewportRef.value
  if (!vp) return
  isPointerDown = true
  pointerId = e.pointerId
  (e.target as Element).setPointerCapture(pointerId)
  startX = e.clientX
  startScroll = vp.scrollLeft
  vp.classList.add('dragging')
}

function onPointerMove(e: PointerEvent) {
  if (!isPointerDown) return
  if (pointerId !== e.pointerId) return
  const vp = viewportRef.value
  if (!vp) return
  const dx = e.clientX - startX
  vp.scrollLeft = startScroll - dx
}

function onPointerUp(e?: PointerEvent) {
  if (!isPointerDown) return
  const vp = viewportRef.value
  if (pointerId != null && vp) {
    try {
      vp.releasePointerCapture(pointerId)
    } catch {}
  }
  isPointerDown = false
  pointerId = null
  if (vp) vp.classList.remove('dragging')
}

// listeners
onMounted(() => {
  recalc()
  window.addEventListener('resize', recalc, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('resize', recalc)
})
</script>

<style scoped>
section { padding-top: 5rem; padding-bottom: 5rem; }

/* esconder barra de rolagem visualmente mantendo funcionalidade */
.no-scrollbar {
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar { display: none; }

/* snap para facilitar leitura slide-a-slide */
.track { scroll-snap-type: x mandatory; }
.scroll-card {
  scroll-snap-align: start;
  /* garante altura e espaçamento similar ao mock */
  min-height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* pequeno feedback visual ao arrastar */
.dragging { cursor: grabbing; user-select: none; }

/* ajuste mínimo para o texto e tamanho do card */
.min-h-\[120px\] { min-height: 120px; }

/* botões maiores em mobile para acessibilidade */
button[aria-label="Anterior"],
button[aria-label="Próximo"] {
  width: 40px;
  height: 40px;
  font-size: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* melhora o espaçamento interno para telas maiores */
@media (min-width: 768px) {
  section { padding-top: 6rem; padding-bottom: 6rem; }
  .scroll-card { min-height: 220px; }
}
</style>