<template>
  <section class="py-20 bg-emerald-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl md:text-5xl font-serif font-bold text-emerald-900 text-center">O Que Dizem Nossos Clientes</h2>
      <p class="text-center text-sm text-emerald-700 mt-2">Depoimentos reais de quem confia em nossos serviços</p>

      <!-- carousel: scrollable, drag-to-scroll (mouse / touch / pointer) -->
      <div class="relative mt-8">
        <div
          ref="viewportRef"
          class="overflow-x-auto no-scrollbar"
          @pointerdown="onPointerDown"
          @pointermove="onPointerMove"
          @pointerup="onPointerUp"
          @pointercancel="onPointerUp"
          @pointerleave="onPointerUp"
          @mousedown="onPointerDown"
          @mousemove="onPointerMove"
          @mouseup="onPointerUp"
          @touchstart.prevent="onPointerDown"
          @touchmove.passive="onPointerMove"
          @touchend="onPointerUp"
        >
          <div
            ref="trackRef"
            class="flex items-stretch gap-6 px-2"
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
}

function scrollNext() {
  const vp = viewportRef.value
  if (!vp) return
  const amount = Math.floor(vp.clientWidth * 0.9)
  vp.scrollBy({ left: amount, behavior: 'smooth' })
}

function scrollPrev() {
  const vp = viewportRef.value
  if (!vp) return
  const amount = Math.floor(vp.clientWidth * 0.9)
  vp.scrollBy({ left: -amount, behavior: 'smooth' })
}

// helper to get clientX from different event types
function getClientX(e: PointerEvent | MouseEvent | TouchEvent): number {
  if ('touches' in e && e.touches && e.touches.length) {
    return e.touches[0].clientX
  }
  if ('changedTouches' in e && e.changedTouches && e.changedTouches.length) {
    return e.changedTouches[0].clientX
  }
  // PointerEvent and MouseEvent
  return (e as PointerEvent).clientX ?? (e as MouseEvent).clientX ?? 0
}

function onPointerDown(e: PointerEvent | MouseEvent | TouchEvent) {
  const vp = viewportRef.value
  if (!vp) return

  isPointerDown = true

  // capture pointerId only if event has it (PointerEvent)
  if ('pointerId' in e && typeof (e as any).pointerId === 'number') {
    pointerId = (e as PointerEvent).pointerId
    // try to set pointer capture when supported
    try {
      const target = e.target as Element & { setPointerCapture?: (id: number) => void }
      if (typeof target.setPointerCapture === 'function') target.setPointerCapture(pointerId)
    } catch {
      // ignore if not supported or fails
      pointerId = null
    }
  } else {
    pointerId = null
  }

  startX = getClientX(e)
  startScroll = vp.scrollLeft
  vp.classList.add('dragging')
}

function onPointerMove(e: PointerEvent | MouseEvent | TouchEvent) {
  if (!isPointerDown) return
  const vp = viewportRef.value
  if (!vp) return

  const clientX = getClientX(e)
  const dx = clientX - startX
  vp.scrollLeft = startScroll - dx
}

function onPointerUp(e?: PointerEvent | MouseEvent | TouchEvent) {
  if (!isPointerDown) return
  const vp = viewportRef.value
  if (pointerId != null && vp) {
    try {
      const target = (e && (e.target as Element)) || vp
      if (typeof (target as any).releasePointerCapture === 'function') {
        (target as any).releasePointerCapture(pointerId)
      }
    } catch {
      // ignore
    }
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