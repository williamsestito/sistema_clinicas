<template>
  <section id="sobre" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <!-- imagem à esquerda -->
        <div class="order-1 md:order-1">
          <div class="rounded-2xl overflow-hidden shadow-lg">
            <img :src="sectionImage" alt="Clínica Duda" class="w-full h-auto object-cover block" />
          </div>
        </div>

        <!-- texto à direita -->
        <div class="order-2 md:order-2">
          <h2 class="text-3xl md:text-4xl font-serif font-bold text-emerald-900 mb-4">{{ sectionTitle }}</h2>

          <div class="text-sm text-emerald-700 space-y-4 leading-relaxed" v-html="sectionContent"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { SiteSection } from '@/services/publicApi'

const props = defineProps<{
  section?: SiteSection
  image?: string
}>()

const sectionTitle = computed(() => props.section?.title || 'Sobre a Clínica Duda')
const sectionContent = computed(() => props.section?.content || '<p>Cuidado especializado em podologia e estética.</p>')
const sectionImage = computed(() => props.section?.image_url || props.image || '/about.jpg')
</script>

<style scoped>
/* pequenas correções para garantir aspecto igual ao mock */
section { padding-top: 5rem; padding-bottom: 5rem; }
@media (min-width: 768px) {
  img { height: 420px; object-fit: cover; }
}
</style>